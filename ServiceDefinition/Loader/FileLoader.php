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

use Bundle\WebServiceBundle\Util\Assert;

abstract class FileLoader implements LoaderInterface
{
    protected $file;

    public function __construct($file)
    {
        Assert::thatArgument('file', file_exists($file), 'The service definition file %s does not exist');
        Assert::thatArgument('file', is_readable($file), 'The service definition file %s is not readable');

        $this->file = $file;
    }
}