<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * BeSimpleSoapExtension.
 *
 * @author Christian Kerl <christian-kerl@web.de>
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

        $loader->load('loaders.xml');
        $loader->load('converters.xml');
        $loader->load('webservice.xml');

        $processor     = new Processor();
        $configuration = new Configuration();

        $config = $processor->process($configuration->getConfigTree(), $configs);

        $container->setParameter('besimple.soap.definition.dumper.options.stylesheet', $config['wsdl_dumper']['stylesheet']);

        foreach($config['services'] as $name => $serviceConfig) {
            $serviceConfig['name'] = $name;
            $this->createWebServiceContext($serviceConfig, $container);
        }
    }

    private function createWebServiceContext(array $config, ContainerBuilder $container)
    {
        $bindingSuffix = $this->bindingConfigToServiceSuffixMap[$config['binding']];
        unset($config['binding']);

        $contextId  = 'besimple.soap.context.'.$config['name'];
        $definition = new DefinitionDecorator('besimple.soap.context.'.$bindingSuffix);
        $context    = $container->setDefinition($contextId, $definition);

        $options = $container
            ->getDefinition('besimple.soap.context.'.$bindingSuffix)
            ->getArgument(4);

        $definition->replaceArgument(4, array_merge($options, $config));
    }
}
