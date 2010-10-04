<?php

namespace Bundle\WebServiceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WebServiceExtension extends Extension
{

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