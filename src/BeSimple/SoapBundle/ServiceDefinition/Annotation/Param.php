<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Annotation;

/**
 * @Annotation
 */
class Param extends Configuration implements TypedElementInterface
{
    private $value;
    private $phpType;
    private $xmlType;
    private $isNillable = false;

    public function getValue()
    {
        return $this->value;
    }

    public function getPhpType()
    {
        return $this->phpType;
    }

    public function getXmlType()
    {
        return $this->xmlType;
    }

    public function isNillable()
    {
        return $this->isNillable;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setPhpType($phpType)
    {
        $this->phpType = $phpType;
    }

    public function setXmlType($xmlType)
    {
        $this->xmlType = $xmlType;
    }

    public function setNillable($isNillable)
    {
        $this->isNillable = (bool) $isNillable;
    }

    public function getAliasName()
    {
        return 'param';
    }
}