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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * WebServiceExtension.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class WebServiceExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

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