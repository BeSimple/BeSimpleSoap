<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle;

use BeSimple\SoapBundle\ServiceBinding\ServiceBinder;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;
use BeSimple\SoapWsdl\Dumper\Dumper;
use BeSimple\SoapServer\SoapServerBuilder;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * WebServiceContext.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class WebServiceContext
{
    private $options;

    private $serviceDefinition;
    private $serviceBinder;
    private $serverBuilder;

    public function __construct(LoaderInterface $loader, TypeConverterCollection $converters, array $options)
    {
        $this->loader = $loader;
        $this->converters = $converters;
        $this->options = $options;
    }

    public function getServiceDefinition()
    {
        if (null === $this->serviceDefinition) {
            $cache = new ConfigCache(sprintf('%s/%s.definition.php', $this->options['cache_dir'], $this->options['name']), $this->options['debug']);
            if ($cache->isFresh()) {
                $this->serviceDefinition = include (string) $cache;
            } else {
                if (!$this->loader->supports($this->options['resource'], $this->options['resource_type'])) {
                    throw new \LogicException(sprintf('Cannot load "%s" (%s)', $this->options['resource'], $this->options['resource_type']));
                }

                $this->serviceDefinition = $this->loader->load($this->options['resource'], $this->options['resource_type']);
                $this->serviceDefinition->setName($this->options['name']);
                $this->serviceDefinition->setNamespace($this->options['namespace']);

                $cache->write('<?php return unserialize('.var_export(serialize($this->serviceDefinition), true).');');
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
        $file      = sprintf ('%s/%s.%s.wsdl', $this->options['cache_dir'], $this->options['name'], md5($endpoint));
        $cache = new ConfigCache($file, $this->options['debug']);

        if(!$cache->isFresh()) {
            $definition = $this->getServiceDefinition();

            if ($endpoint) {
                $definition->setOption('location', $endpoint);
            }

            $dumper = new Dumper($definition, array('stylesheet' => $this->options['wsdl_stylesheet']));
            $cache->write($dumper->dump());
        }

        return (string) $cache;
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
                ->withClassmap($this->getServiceDefinition()->getTypeRepository()->getClassmap())
                ->withTypeConverters($this->converters)
            ;

            if (null !== $this->options['cache_type']) {
                $this->serverBuilder->withWsdlCache($this->options['cache_type']);
            }
        }

        return $this->serverBuilder;
    }
}
