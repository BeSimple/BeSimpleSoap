<?php

namespace Bundle\WebServiceBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * WebServiceBundle.
 *
 * @author Christian Kerl
 */
class WebServiceBundle extends Bundle
{
    public function registerExtensions(ContainerBuilder $container)
    {
        parent::registerExtensions($container);
    }
}