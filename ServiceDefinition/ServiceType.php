<?php

namespace Bundle\WebServiceBundle\ServiceDefinition;

class ServiceType
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

    public function setPhpType($value)
    {
        $this->phpType = $value;
    }

    public function getXmlType()
    {
        return $this->xmlType;
    }

    public function setXmlType($value)
    {
        $this->xmlType = $value;
    }

    public function getConverter()
    {
        return $this->converter;
    }

    public function setConverter($value)
    {
        $this->converter = $value;
    }
}