<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Dumper;

use Bundle\WebServiceBundle\ServiceDefinition\Dumper\DumperInterface;
use Bundle\WebServiceBundle\Util\Assert;

/**
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
abstract class FileDumper implements DumperInterface
{
    protected $file;

    public function __construct($file)
    {
        Assert::thatArgumentNotNull('file', $file);

        $this->file = $file;
    }
}
