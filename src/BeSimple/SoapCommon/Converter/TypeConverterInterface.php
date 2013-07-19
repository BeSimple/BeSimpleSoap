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
 * Type converter interface.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
interface TypeConverterInterface
{
    /**
     * Get type namespace.
     *
     * @return string
     */
    function getTypeNamespace();

    /**
     * Get type name.
     *
     * @return string
     */
    function getTypeName();

    /**
     * Convert given XML string to PHP type.
     *
     * @param string $data XML string
     *
     * @return mixed
     */
    function convertXmlToPhp($data);

    /**
     * Convert PHP type to XML string.
     *
     * @param mixed $data PHP type
     *
     * @return string
     */
    function convertPhpToXml($data);
}