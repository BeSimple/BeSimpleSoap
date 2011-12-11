<?php

/*
 * This file is part of the BeSimpleSoapClient.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapClient;

use BeSimple\SoapCommon\SoapResponse as CommonSoapResponse;
use BeSimple\SoapCommon\SoapMessage;

/**
 * SoapResponse class for SoapClient. Provides factory function for response object.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class SoapResponse extends CommonSoapResponse
{
    /**
     * Factory function for SoapResponse.
     *
     * @param string $content
     * @param string $location
     * @param string $action
     * @param string $version
     * @param string $contentType
     * @return BeSimple\SoapClient\SoapResponse
     */
    public static function create($content, $location, $action, $version, $contentType)
    {
        $response = new SoapResponse();
        $response->setContent($content);
        $response->setLocation($location);
        $response->setAction($action);
        $response->setVersion($version);
        $response->setContentType($contentType);

        return $response;
    }
}