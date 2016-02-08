<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class BsFloat extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("float")
     */
    protected $value;
}
