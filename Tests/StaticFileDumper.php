<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Tests;

use Bundle\WebServiceBundle\ServiceDefinition\ServiceDefinition;
use Bundle\WebServiceBundle\ServiceDefinition\Dumper\DumperInterface;

class StaticFileDumper implements DumperInterface
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function dumpServiceDefinition(ServiceDefinition $definition)
    {
        return $this->file;
    }
}