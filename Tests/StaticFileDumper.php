<?php

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