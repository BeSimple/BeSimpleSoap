<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged webservice.definition.loader services to ebservice.definition.resolver service
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class WebServiceResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('webservice.definition.loader.resolver')) {
            return;
        }

        $definition = $container->getDefinition('webservice.definition.loader.resolver');

        foreach ($container->findTaggedServiceIds('webservice.definition.loader') as $id => $attributes) {
            $definition->addMethodCall('addLoader', array(new Reference($id)));
        }
    }
}
