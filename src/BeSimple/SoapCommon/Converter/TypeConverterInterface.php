<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Converter;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
interface TypeConverterInterface
{
    function getTypeNamespace();

    function getTypeName();

    function convertXmlToPhp($data);

    function convertPhpToXml($data);
}