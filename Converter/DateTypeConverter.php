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
 * @author Francis Besset <francis.besset@gmail.com>
 */
class DateTypeConverter implements TypeConverterInterface
{
    public function getTypeNamespace()
    {
        return 'http://www.w3.org/2001/XMLSchema';
    }

    public function getTypeName()
    {
        return 'date';
    }

    public function convertXmlToPhp(SoapRequest $request, $data)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($data);

        return new \DateTime($doc->textContent);
    }

    public function convertPhpToXml(SoapResponse $response, $data)
    {
        return sprintf('<%1$s>%2$s</%1$s>', $this->getTypeName(), $data->format('Y-m-d'));
    }
}
