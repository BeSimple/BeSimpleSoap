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