<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Converter;

use Bundle\WebServiceBundle\Soap\SoapRequest;

use Bundle\WebServiceBundle\Soap\SoapResponse;

/**
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
interface TypeConverterInterface
{
    function getTypeNamespace();

    function getTypeName();

    function convertXmlToPhp(SoapRequest $request, $data);

    function convertPhpToXml(SoapResponse $response, $data);
}
