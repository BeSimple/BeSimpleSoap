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
            $loader = new XmlFileLoader($configuration, __DIR__ . "/../Resources/config");
            $loader->load("services.xml");

            $configuration->setAlias("http_kernel", "webservice_http_kernel");
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