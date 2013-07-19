<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged besimple.soap.converter services to besimple.soap.converter.repository service
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class TypeConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('besimple.soap.converter.collection')) {
            return;
        }

        $definition = $container->getDefinition('besimple.soap.converter.collection');

        foreach ($container->findTaggedServiceIds('besimple.soap.converter') as $id => $attributes) {
            $definition->addMethodCall('add', array(new Reference($id)));
        }
    }
}
