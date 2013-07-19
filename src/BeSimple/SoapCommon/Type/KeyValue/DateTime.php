<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class DateTime extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("dateTime")
     */
    protected $value;
}
