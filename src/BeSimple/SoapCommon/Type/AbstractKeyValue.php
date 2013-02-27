<?php

namespace BeSimple\SoapCommon\Type;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

abstract class AbstractKeyValue
{
    /**
     * @Soap\ComplexType("string")
     */
    protected $key;

    /**
     * The Soap type of this variable must be defined in child class
     */
    protected $value;

    public function __construct($key, $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }
}
