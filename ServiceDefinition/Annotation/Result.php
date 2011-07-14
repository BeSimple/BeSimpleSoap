<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Annotation;

class Result extends TypedElement
{
    public function __construct($values)
    {
        parent::__construct($values);
    }
}