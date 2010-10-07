<?php

namespace Bundle\WebServiceBundle\ServiceDefinition\Dumper;

use Bundle\WebServiceBundle\ServiceDefinition\ServiceDefinition;

interface DumperInterface
{
    function dumpServiceDefinition(ServiceDefinition $definition);
}