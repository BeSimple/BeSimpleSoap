<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle;


use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;

use Bundle\WebServiceBundle\Converter\ConverterRepository;
use Bundle\WebServiceBundle\ServiceBinding\ServiceBinder;
use Bundle\WebServiceBundle\ServiceBinding\MessageBinderInterface;
use Bundle\WebServiceBundle\ServiceDefinition\Dumper\DumperInterface;
use Bundle\WebServiceBundle\Soap\SoapServerFactory;

/**
 * WebServiceContext.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class WebServiceContext
{
    private $converterRepository;
    private $requestMessageBinder;
    private $responseMessageBinder;

    private $serviceDefinitionLoader;
    private $wsdlFileDumper;

    private $options;

    private $serviceDefinition;
    private $serviceBinder;
    private $serverFactory;

    public function __construct(LoaderInterface $loader, DumperInterface $dumper, ConverterRepository $converterRepository, MessageBinderInterface $requestMessageBinder, MessageBinderInterface $responseMessageBinder, array $options)
    {
        $this->serviceDefinitionLoader = $loader;
        $this->wsdlFileDumper = $dumper;

        $this->converterRepository = $converterRepository;
        $this->requestMessageBinder = $requestMessageBinder;
        $this->responseMessageBinder = $responseMessageBinder;

        $this->options = $options;
    }

    public function getServiceDefinition()
    {
        if($this->serviceDefinition === null) {
            if(!$this->serviceDefinitionLoader->supports($this->options['resource'], $this->options['resource_type'])) {
                throw new \LogicException();
            }

            $this->serviceDefinition = $this->serviceDefinitionLoader->load($this->options['resource'], $this->options['resource_type']);
            $this->serviceDefinition->setName($this->options['name']);
            $this->serviceDefinition->setNamespace($this->options['namespace']);
        }

        return $this->serviceDefinition;
    }

    public function getWsdlFile($endpoint = null)
    {
        $id = $endpoint !== null ? '.' . md5($endpoint) : '';
        $file = sprintf('%s/%s.wsdl', $this->options['cache_dir'], $this->options['name'] . $id);
        $cache = new ConfigCache($file, true);

        if(!$cache->isFresh()) {
            $cache->write($this->wsdlFileDumper->dumpServiceDefinition($this->getServiceDefinition(), array('endpoint' => $endpoint)));
        }

        return $file;
    }

    public function getWsdlFileContent($endpoint = null)
    {
        return file_get_contents($this->getWsdlFile($endpoint));
    }

    public function getServiceBinder()
    {
        if($this->serviceBinder === null) {
            $this->serviceBinder = new ServiceBinder($this->getServiceDefinition(), $this->requestMessageBinder, $this->responseMessageBinder);
        }

        return $this->serviceBinder;
    }

    public function getServerFactory()
    {
        if($this->serverFactory === null) {
            $this->serverFactory = new SoapServerFactory($this->getServiceDefinition(), $this->getWsdlFile(), $this->converterRepository);
        }

        return $this->serverFactory;
    }
}