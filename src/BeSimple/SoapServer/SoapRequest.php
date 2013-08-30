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

namespace BeSimple\SoapServer;

use BeSimple\SoapCommon\SoapRequest as CommonSoapRequest;
use BeSimple\SoapCommon\SoapMessage;

/**
 * SoapRequest class for SoapClient. Provides factory function for request object.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class SoapRequest extends CommonSoapRequest
{
    /**
     * Factory function for SoapRequest.
     *
     * @param string $content Content
     * @param string $version SOAP version
     *
     * @return BeSimple\SoapClient\SoapRequest
     */
    public static function create($content, $version)
    {
        $location = self::getCurrentUrl();
        /*
         * Work around missing header/php://input access in PHP cli webserver by
         * setting headers additionally as GET parameters and SOAP request body
         * explicitly as POST variable
         */
        if (php_sapi_name() == "cli-server") {
            $content = is_null($content) ? $_POST['request'] : $content;
            $action = $_GET[SoapMessage::SOAP_ACTION_HEADER];
            $contentType = $_GET[SoapMessage::CONTENT_TYPE_HEADER];
        } else {
            $content = is_null($content) ? file_get_contents("php://input") : $content;
            $action = $_SERVER[SoapMessage::SOAP_ACTION_HEADER];
            $contentType = $_SERVER[SoapMessage::CONTENT_TYPE_HEADER];
        }

        $request = new SoapRequest();
        // $content is if unmodified from SoapClient not a php string type!
        $request->setContent((string) $content);
        $request->setLocation($location);
        $request->setAction($action);
        $request->setVersion($version);
        $request->setContentType($contentType);

        return $request;
    }

    /**
     * Builds the current URL from the $_SERVER array.
     *
     * @return string
     */
    public static function getCurrentUrl()
    {
        $url = '';
        if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] === '1')) {
            $url .= 'https://';
        } else {
            $url .= 'http://';
        }
        $url .= isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : '';
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) {
            $url .= ":{$_SERVER['SERVER_PORT']}";
        }
        $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        return $url;
    }
}