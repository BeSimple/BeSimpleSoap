<?php

namespace Bundle\WebServiceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WebServiceExtension extends Extension
{
    public function configLoad(array $config, ContainerBuilder $configuration)
    {
        $loader = new XmlFileLoader($configuration, __DIR__ . "/../Resources/config");
        $loader->load("services.xml");

        $configuration->setAlias("http_kernel", "webservice_http_kernel");
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