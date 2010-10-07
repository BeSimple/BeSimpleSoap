<?php

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