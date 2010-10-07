<?php

namespace Bundle\WebServiceBundle\ServiceDefinition\Loader;

abstract class FileLoader implements LoaderInterface
{
    protected $file;

    public function __construct($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('The service definition file %s does not exist', $file));
        }

        if (!is_readable($file)) {
            throw new \InvalidArgumentException(sprintf('The service definition file %s is not readable', $file));
        }

        $this->file = $file;
    }
}