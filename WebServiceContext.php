<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle;

use BeSimple\SoapBundle\Converter\TypeRepository;
use BeSimple\SoapBundle\ServiceBinding\MessageBinderInterface;
use BeSimple\SoapBundle\ServiceBinding\ServiceBinder;
use BeSimple\SoapBundle\ServiceDefinition\Dumper\DumperInterface;

use BeSimple\SoapCommon\Classmap;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;
use BeSimple\SoapServer\SoapServerBuilder;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * WebServiceContext.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class WebServiceContext
{
    private $classmap;

    private $typeRepository;
    private $converterRepository;

    private $wsdlFileDumper;

    private $options;

    private $serviceDefinition;
    private $serviceBinder;
    private $serverBuilder;

    public function __construct(LoaderInterface $loader, DumperInterface $dumper, Classmap $classmap, TypeRepository $typeRepository, TypeConverterCollection $converters, array $options) {
        $this->loader         = $loader;
        $this->wsdlFileDumper = $dumper;

        $this->classmap       = $classmap;

        $this->typeRepository = $typeRepository;
        $this->converters     = $converters;

        $this->options = $options;
    }

    public function getServiceDefinition()
    {
        if (null === $this->serviceDefinition) {
            $cacheDefinition = new ConfigCache(sprintf('%s/%s.definition.php', $this->options['cache_dir'], $this->options['name']), $this->options['debug']);
            if ($cacheDefinition->isFresh()) {
                $this->serviceDefinition = include (string) $cacheDefinition;
            } else {
                if (!$this->loader->supports($this->options['resource'], $this->options['resource_type'])) {
                    throw new \LogicException(sprintf('Cannot load "%s" (%s)', $this->options['resource'], $this->options['resource_type']));
                }

                $this->serviceDefinition = $this->loader->load($this->options['resource'], $this->options['resource_type']);
                $this->serviceDefinition->setName($this->options['name']);
                $this->serviceDefinition->setNamespace($this->options['namespace']);

                $this->serviceDefinition->setClassmap($this->classmap);
                $this->classmap = null;

                $this->typeRepository->fixTypeInformation($this->serviceDefinition);
            }
        }

        return $this->serviceDefinition;
    }

    public function getWsdlFileContent($endpoint = null)
    {
        return file_get_contents($this->getWsdlFile($endpoint));
    }

    public function getWsdlFile($endpoint = null)
    {
        $file      = sprintf('%s/%s.%s.wsdl', $this->options['cache_dir'], $this->options['name'], md5($endpoint));
        $cacheWsdl = new ConfigCache($file, $this->options['debug']);

        if(!$cacheWsdl->isFresh()) {
            $serviceDefinition = $this->getServiceDefinition();

            $cacheWsdl->write($this->wsdlFileDumper->dumpServiceDefinition($serviceDefinition, $endpoint));

            $cacheDefinition = new ConfigCache(sprintf('%s/%s.definition.php', $this->options['cache_dir'], $this->options['name']), $this->options['debug']);
            $cacheDefinition->write('<?php return unserialize('.var_export(serialize($serviceDefinition), true).');');
        }

        return (string) $cacheWsdl;
    }

    public function getServiceBinder()
    {
        if (null === $this->serviceBinder) {
            $this->serviceBinder = new ServiceBinder(
                $this->getServiceDefinition(),
                new $this->options['binder_request_header_class'](),
                new $this->options['binder_request_class'](),
                new $this->options['binder_response_class']()
            );
        }

        return $this->serviceBinder;
    }

    public function getServerBuilder()
    {
        if (null === $this->serverBuilder) {
            $this->serverBuilder = SoapServerBuilder::createWithDefaults()
                ->withWsdl($this->getWsdlFile())
                ->withClassmap($this->getServiceDefinition()->getClassmap())
                ->withTypeConverters($this->converters)
            ;

            if (null !== $this->options['cache_type']) {
                $this->serverBuilder->withWsdlCache($this->options['cache_type']);
            }
        }

        return $this->serverBuilder;
    }
}
