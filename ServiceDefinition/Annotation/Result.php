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
class Result extends Configuration implements TypedElementInterface
{
    private $phpType;
    private $xmlType;

    public function getPhpType()
    {
        return $this->phpType;
    }

    public function getXmlType()
    {
        return $this->xmlType;
    }

    public function setPhpType($phpType)
    {
        $this->phpType = $phpType;
    }

    public function setXmlType($xmlType)
    {
        $this->xmlType = $xmlType;
    }

    public function getAliasName()
    {
        return 'result';
    }
}