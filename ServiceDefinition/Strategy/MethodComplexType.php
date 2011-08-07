<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Strategy;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class MethodComplexType extends BaseComplexType
{
    private $setter;

    public function getSetter()
    {
        return $this->setter;
    }

    public function setSetter($setter)
    {
        $this->setter = $setter;
    }
}