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

use BeSimple\SoapCommon\SoapKernel;

/**
 * Internal type converter interface.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 * @author Christian Kerl <christian-kerl@web.de>
 */
interface InternalTypeConverterInterface
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
     * @param string                          $data       XML string
     * @param \BeSimple\SoapCommon\SoapKernel $soapKernel SoapKernel instance
     *
     * @return mixed
     */
    function convertXmlToPhp($data, SoapKernel $soapKernel);

    /**
     * Convert PHP type to XML string.
     *
     * @param mixed                           $data       PHP type
     * @param \BeSimple\SoapCommon\SoapKernel $soapKernel SoapKernel instance
     *
     * @return string
     */
    function convertPhpToXml($data, SoapKernel $soapKernel);
}