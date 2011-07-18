<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle;

use BeSimple\SoapBundle\DependencyInjection\Compiler\WebServiceResolverPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * BeSimpleSoapBundle.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class BeSimpleSoapBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new WebServiceResolverPass());
    }
}
