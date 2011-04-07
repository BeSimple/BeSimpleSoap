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
        
        foreach($config['services'] as $serviceContextConfig)
        {
            $this->createWebServiceContext($serviceContextConfig, $container);
        }
    }

    private function createWebServiceContext(array $config, ContainerBuilder $container)
    {
        $bindingDependendArguments = array(1, 3, 4);
        $bindingSuffix = $this->bindingConfigToServiceSuffixMap[$config['binding']];
        unset($config['binding']);
        
        $contextPrototype = $container->getDefinition('webservice.context');
        $contextPrototypeArguments = $contextPrototype->getArguments();
        
        $contextId = 'webservice.context.' . $config['name'];
        $context = $container->setDefinition($contextId, new DefinitionDecorator('webservice.context'));
                
        foreach($bindingDependendArguments as $idx)
        {
            $context->setArgument($idx, new Reference($contextPrototypeArguments[$idx] . $bindingSuffix));
        }
        $context->setArgument(5, array_merge($contextPrototypeArguments[5], $config));
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