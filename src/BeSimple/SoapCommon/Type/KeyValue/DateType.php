<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class Date extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("date")
     */
    protected $value;
}
