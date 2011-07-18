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

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
interface TypeConverterInterface
{
    function getTypeNamespace();

    function getTypeName();

    function convertXmlToPhp(SoapRequest $request, $data);

    function convertPhpToXml(SoapResponse $response, $data);
}