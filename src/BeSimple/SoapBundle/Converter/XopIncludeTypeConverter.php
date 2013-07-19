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
class XopIncludeTypeConverter implements TypeConverterInterface
{
    public function getTypeNamespace()
    {
        return 'http://www.w3.org/2001/XMLSchema';
    }

    public function getTypeName()
    {
        return 'base64Binary';
    }

    public function convertXmlToPhp(SoapRequest $request, $data)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($data);

        $includes = $doc->getElementsByTagNameNS('http://www.w3.org/2004/08/xop/include', 'Include');
        $include = $includes->item(0);

        $ref = $include->getAttribute('href');

        if (String::startsWith($ref, 'cid:')) {
            $cid = urldecode(substr($ref, 4));

            return $request->getSoapAttachments()->get($cid)->getContent();
        }

        return $data;
    }

    public function convertPhpToXml(SoapResponse $response, $data)
    {
        return $data;
    }
}