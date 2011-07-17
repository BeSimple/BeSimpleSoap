<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * WebServiceExtension.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class WebServiceExtension extends Extension
{
    private $contextArguments;

    // maps config options to service suffix'
    private $bindingConfigToServiceSuffixMap = array('rpc-literal' => '.rpcliteral', 'document-wrapped' => '.documentwrapped');

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('loaders.xml');
        $loader->load('webservice.xml');

        $processor     = new Processor();
        $configuration = new Configuration();

        $config = $processor->process($configuration->getConfigTree(), $configs);

        foreach($config['services'] as $name => $serviceConfig) {
            $serviceConfig['name'] = $name;
            $this->createWebServiceContext($serviceConfig, $container);
        }
    }

    private function createWebServiceContext(array $config, ContainerBuilder $container)
    {
        $bindingSuffix = $this->bindingConfigToServiceSuffixMap[$config['binding']];
        unset($config['binding']);

        if (null === $this->contextArguments) {
            $this->contextArguments = $container
                ->getDefinition('webservice.context')
                ->getArguments()
            ;
        }

        $contextId = 'webservice.context.'.$config['name'];
        $context   = $container->setDefinition($contextId, $definition = new DefinitionDecorator('webservice.context'));

        $arguments = array();
        foreach($this->contextArguments as $i => $argument) {
            if (in_array($i, array(1, 3, 4))) {
                $argument = new Reference($argument->__toString().$bindingSuffix);
            } elseif (5 === $i) {
                $argument = array_merge($argument, $config);
            } else {
                $argument = new Reference($argument->__toString());
            }

            $definition->replaceArgument($i, $argument);
        }
    }
}