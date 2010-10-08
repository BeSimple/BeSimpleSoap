<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Loader;

use Bundle\WebServiceBundle\ServiceDefinition\ServiceDefinition;

interface LoaderInterface
{
    /**
     * Loads the contents of the given ServiceDefinition.
     *
     * @param ServiceDefinition $definition
     */
    function loadServiceDefinition(ServiceDefinition $definition);
}