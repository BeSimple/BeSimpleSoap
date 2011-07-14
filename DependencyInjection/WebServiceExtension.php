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

use Bundle\WebServiceBundle\Util\Assert;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

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
    // maps config options to service suffix'
    private $bindingConfigToServiceSuffixMap = array('rpc-literal' => '.rpcliteral', 'document-wrapped' => '.documentwrapped');

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('annotations.xml');
        $loader->load('webservice.xml');

        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->process($configuration->getConfigTree(), $configs);

        foreach($config['services'] as $name => $serviceConfig) {
            $this->createWebServiceContext($name, $serviceConfig, $container);
        }
    }

    private function createWebServiceContext($name, array $config, ContainerBuilder $container)
    {
        $bindingDependentArguments = array(1, 3, 4);
        $bindingSuffix = $this->bindingConfigToServiceSuffixMap[$config['binding']];
        unset($config['binding']);

        $contextPrototype = $container->getDefinition('webservice.context');
        $contextPrototypeArguments = $contextPrototype->getArguments();

        $contextId = 'webservice.context.'.$name;
        $context = $container->setDefinition($contextId, new DefinitionDecorator('webservice.context'));

        $arguments = array();
        foreach($bindingDependentArguments as $idx) {
            $arguments[] = new Reference($contextPrototypeArguments[$idx].$bindingSuffix);
        }
        $arguments[5] = array_merge($contextPrototypeArguments[5], $config);

        $context->setArguments($arguments);
    }

    public function getNamespace()
    {
        return null;
    }

    public function getXsdValidationBasePath()
    {
        return null;
    }
}