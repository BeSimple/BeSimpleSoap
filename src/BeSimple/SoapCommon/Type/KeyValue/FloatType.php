<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class Float extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("float")
     */
    protected $value;
}
