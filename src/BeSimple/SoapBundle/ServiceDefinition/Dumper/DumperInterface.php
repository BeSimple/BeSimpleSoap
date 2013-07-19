<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Dumper;

use BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition;

interface DumperInterface
{
    function dumpServiceDefinition(ServiceDefinition $definition, $endpoint);
}