<?php

namespace BeSimple\SoapCommon\Type\KeyValue;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use BeSimple\SoapCommon\Type\AbstractKeyValue;

class BsInt extends AbstractKeyValue
{
    /**
     * @Soap\ComplexType("int")
     */
    protected $value;
}
