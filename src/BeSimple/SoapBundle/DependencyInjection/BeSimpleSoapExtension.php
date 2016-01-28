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

namespace BeSimple\SoapBundle\DependencyInjection;

use BeSimple\SoapCommon\Cache;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;

/**
 * BeSimpleSoapExtension.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class BeSimpleSoapExtension extends Extension
{
    // maps config options to service suffix
    private $bindingConfigToServiceSuffixMap = array(
        'rpc-literal'      => 'rpcliteral',
        'document-wrapped' => 'documentwrapped',
    );

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('request.xml');

        $loader->load('loaders.xml');
        $loader->load('converters.xml');
        $loader->load('webservice.xml');

        $processor     = new Processor();
        $configuration = new Configuration();

        $config = $processor->process($configuration->getConfigTree(), $configs);

        $this->registerCacheConfiguration($config['cache'], $container, $loader);

        if (!empty($config['clients'])) {
            $this->registerClientConfiguration($config['clients'], $container, $loader);
        }

        $container->setParameter('besimple.soap.definition.dumper.options.stylesheet', $config['wsdl_dumper']['stylesheet']);

        foreach($config['services'] as $name => $serviceConfig) {
            $serviceConfig['name'] = $name;
            $this->createWebServiceContext($serviceConfig, $container);
        }

        $container->setParameter('besimple.soap.exception_listener.controller', $config['exception_controller']);
    }

    private function registerCacheConfiguration(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $loader->load('soap.xml');

        $config['type'] = $this->getCacheType($config['type']);

        foreach (array('type', 'lifetime', 'limit') as $key) {
            $container->setParameter('besimple.soap.cache.'.$key, $config[$key]);
        }
    }

    private function registerClientConfiguration(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        if (3 === Kernel::MAJOR_VERSION) {
            $loader->load('client3.xml');
        } else {
            $loader->load('client.xml');
        }

        foreach ($config as $client => $options) {
            $definition = new DefinitionDecorator('besimple.soap.client.builder');
            $container->setDefinition(sprintf('besimple.soap.client.builder.%s', $client), $definition);

            $definition->replaceArgument(0, $options['wsdl']);

            $defOptions = $container
                    ->getDefinition('besimple.soap.client.builder')
                    ->getArgument(1);

            foreach (array('cache_type', 'user_agent') as $key) {
                if (isset($options[$key])) {
                    $defOptions[$key] = $options[$key];
                }
            }

            $proxy = $options['proxy'];
            if (false !== $proxy['host']) {
                if (null !== $proxy['auth']) {
                    if ('basic' === $proxy['auth']) {
                        $proxy['auth'] = \CURLAUTH_BASIC;
                    } elseif ('ntlm' === $proxy['auth']) {
                        $proxy['auth'] = \CURLAUTH_NTLM;
                    }
                }

                $definition->addMethodCall('withProxy', array(
                    $proxy['host'], $proxy['port'],
                    $proxy['login'], $proxy['password'],
                    $proxy['auth']
                ));
            }

            if (isset($defOptions['cache_type'])) {
                $defOptions['cache_type'] = $this->getCacheType($defOptions['cache_type']);
            }

            $definition->replaceArgument(1, $defOptions);

            $classmap = $this->createClientClassmap($client, $options['classmap'], $container);
            $definition->replaceArgument(2, new Reference($classmap));

            $this->createClient($client, $container);
        }
    }

    private function createClientClassmap($client, array $classmap, ContainerBuilder $container)
    {
        $definition = new DefinitionDecorator('besimple.soap.classmap');
        $container->setDefinition(sprintf('besimple.soap.classmap.%s', $client), $definition);

        if (!empty($classmap)) {
            $definition->setMethodCalls(array(
                array('set', array($classmap)),
            ));
        }

        return sprintf('besimple.soap.classmap.%s', $client);
    }

    private function createClient($client, ContainerBuilder $container)
    {
        $definition = new DefinitionDecorator('besimple.soap.client');
        $container->setDefinition(sprintf('besimple.soap.client.%s', $client), $definition);

        if (3 === Kernel::MAJOR_VERSION) {
            $definition->setFactory(array(
                new Reference(sprintf('besimple.soap.client.builder.%s', $client)),
                'build'
            ));
        } else {
            $definition->setFactoryService(sprintf('besimple.soap.client.builder.%s', $client));
        }
    }

    private function createWebServiceContext(array $config, ContainerBuilder $container)
    {
        $bindingSuffix = $this->bindingConfigToServiceSuffixMap[$config['binding']];
        unset($config['binding']);

        $contextId  = 'besimple.soap.context.'.$config['name'];
        $definition = new DefinitionDecorator('besimple.soap.context.'.$bindingSuffix);
        $container->setDefinition($contextId, $definition);

        if (isset($config['cache_type'])) {
            $config['cache_type'] = $this->getCacheType($config['cache_type']);
        }

        $options = $container
            ->getDefinition('besimple.soap.context.'.$bindingSuffix)
            ->getArgument(2);

        $definition->replaceArgument(2, array_merge($options, $config));
    }

    private function getCacheType($type)
    {
        switch ($type) {
            case 'none':
                return Cache::TYPE_NONE;

            case 'disk':
                return Cache::TYPE_DISK;

            case 'memory':
                return Cache::TYPE_MEMORY;

            case 'disk_memory':
                return Cache::TYPE_DISK_MEMORY;
        }
    }
}
