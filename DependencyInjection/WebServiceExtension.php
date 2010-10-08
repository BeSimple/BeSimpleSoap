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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * WebServiceExtension.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class WebServiceExtension extends Extension
{
    public function configLoad(array $config, ContainerBuilder $configuration)
    {
        if(!$configuration->hasDefinition('webservice_http_kernel'))
        {
            $loader = new XmlFileLoader($configuration, __DIR__ . '/../Resources/config');
            $loader->load('services.xml');

            $configuration->setAlias('http_kernel', 'webservice.kernel');
        }

        if(isset($config['definition']))
        {
            $this->registerServiceDefinitionConfig($config['definition'], $configuration);
        }
        else
        {
            throw new \InvalidArgumentException();
        }

        if(isset($config['binding']))
        {
            $this->registerServiceBindingConfig($config['binding'], $configuration);
        }
    }

    protected function registerServiceDefinitionConfig(array $config, ContainerBuilder $configuration)
    {
        if(!isset($config['name']))
        {
            throw new \InvalidArgumentException();
        }

        $configuration->setParameter('webservice.definition.name', $config['name']);
        $configuration->setParameter('webservice.definition.resource', isset($config['resource']) ? $config['resource'] : null);
    }

    protected function registerServiceBindingConfig(array $config, ContainerBuilder $configuration)
    {
        $style = isset($config['style']) ? $config['style'] : 'document-literal-wrapped';

        if(!in_array($style, array('document-literal-wrapped', 'rpc-literal')))
        {
            throw new \InvalidArgumentException();
        }

        $binderNamespace = 'Bundle\\WebServiceBundle\\ServiceBinding\\';

        switch ($style)
        {
            case 'document-literal-wrapped':
                $configuration->setParameter('webservice.binder.request.class', $binderNamespace . 'DocumentLiteralWrappedRequestMessageBinder');
                $configuration->setParameter('webservice.binder.response.class', $binderNamespace . 'DocumentLiteralWrappedResponseMessageBinder');
                break;
            case 'rpc-literal':
                $configuration->setParameter('webservice.binder.request.class', $binderNamespace . 'RpcLiteralRequestMessageBinder');
                $configuration->setParameter('webservice.binder.response.class', $binderNamespace . 'RpcLiteralResponseMessageBinder');
                break;
        }
    }

    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return null;
    }

    public function getAlias()
    {
        return 'webservice';
    }
}