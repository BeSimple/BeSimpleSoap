<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Converter;

use BeSimple\SoapBundle\Soap\SoapRequest;
use BeSimple\SoapBundle\Soap\SoapResponse;
use BeSimple\SoapBundle\Util\String;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class DateTimeTypeConverter implements TypeConverterInterface
{
    public function getTypeNamespace()
    {
        return 'http://www.w3.org/2001/XMLSchema';
    }

    public function getTypeName()
    {
        return 'dateTime';
    }

    public function convertXmlToPhp(SoapRequest $request, $data)
    {
        return new \DateTime(strip_tags($data));
    }

    public function convertPhpToXml(SoapResponse $response, $data)
    {
        return sprintf('<dateTime>%s</dateTime>', $data->format('Y-m-d\TH:i:sP'));
    }
}
