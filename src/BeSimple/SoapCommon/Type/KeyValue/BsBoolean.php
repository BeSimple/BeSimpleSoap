<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class BsBoolean extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("boolean")
     */
    protected $value;
}
