<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class BsString extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("string")
     */
    protected $value;
}
