<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition;

class Type
{
    private $phpType;
    private $xmlType;
    private $converter;

    public function __construct($phpType = null, $xmlType = null, $converter = null)
    {
        $this->setPhpType($phpType);
        $this->setXmlType($xmlType);
        $this->setConverter($converter);
    }

    public function getPhpType()
    {
        return $this->phpType;
    }

    public function setPhpType($phpType)
    {
        $this->phpType = $phpType;
    }

    public function getXmlType()
    {
        return $this->xmlType;
    }

    public function setXmlType($xmlType)
    {
        $this->xmlType = $xmlType;
    }

    public function getConverter()
    {
        return $this->converter;
    }

    public function setConverter($converter)
    {
        $this->converter = $converter;
    }
}