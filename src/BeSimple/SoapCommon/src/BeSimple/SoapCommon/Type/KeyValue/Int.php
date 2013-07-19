<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class Int extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("int")
     */
    protected $value;
}
