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

/**
 * Extended SoapClient that uses a a cURL wrapper for all underlying HTTP
 * requests in order to use proper authentication for all requests. This also
 * adds NTLM support. A custom WSDL downloader resolves remote xsd:includes and
 * allows caching of all remote referenced items.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class SoapClient extends \SoapClient
{
    /**
     * Soap version.
     *
     * @var int
     */
    protected $soapVersion = SOAP_1_1;

    /**
     * Tracing enabled?
     *
     * @var boolean
     */
    protected $tracingEnabled = false;

    /**
     * cURL instance.
     *
     * @var \BeSimple\SoapClient\Curl
     */
    protected $curl = null;

    /**
     * Last request headers.
     *
     * @var string
     */
    private $lastRequestHeaders = '';

    /**
     * Last request.
     *
     * @var string
     */
    private $lastRequest = '';

    /**
     * Last response headers.
     *
     * @var string
     */
    private $lastResponseHeaders = '';

    /**
     * Last response.
     *
     * @var string
     */
    private $lastResponse = '';

    /**
     * Constructor.
     *
     * @param string               $wsdl    WSDL file
     * @param array(string=>mixed) $options Options array
     */
    public function __construct($wsdl, array $options = array())
    {
        // tracing enabled: store last request/response header and body
        if (isset($options['trace']) && $options['trace'] === true) {
            $this->tracingEnabled = true;
        }
        // store SOAP version
        if (isset($options['soap_version'])) {
            $this->soapVersion = $options['soap_version'];
        }
        $this->curl = new Curl($options);
        $wsdlFile = $this->loadWsdl($wsdl, $options);
        // we want the exceptions option to be set
        $options['exceptions'] = true;
        // disable obsolete trace option for native SoapClient as we need to do our own tracing anyways
        $options['trace'] = false;
        // disable WSDL caching as we handle WSDL caching for remote URLs ourself
        $options['cache_wsdl'] = WSDL_CACHE_NONE;
        parent::__construct($wsdlFile, $options);
    }


    /**
     * Perform HTTP request with cURL.
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @return string
     */
    private function __doHttpRequest($request, $location, $action)
    {
        // $request is if unmodified from SoapClient not a php string type!
        $request = (string)$request;
        if ($this->soapVersion == SOAP_1_2) {
            $headers = array(
                'Content-Type: application/soap+xml; charset=utf-8',
           );
        } else {
            $headers = array(
                'Content-Type: text/xml; charset=utf-8',
           );
        }
        // add SOAPAction header
        $headers[] = 'SOAPAction: "' . $action . '"';
        // execute request
        $responseSuccessfull = $this->curl->exec($location, $request, $headers);
        // tracing enabled: store last request header and body
        if ($this->tracingEnabled === true) {
            $this->lastRequestHeaders = $curl->getRequestHeaders();
            $this->lastRequest = $request;
        }
        // in case of an error while making the http request throw a soapFault
        if ($responseSuccessfull === false) {
            // get error message from curl
            $faultstring = $this->curl->getErrorMessage();
            throw new \SoapFault('HTTP', $faultstring);
        }
        // tracing enabled: store last response header and body
        if ($this->tracingEnabled === true) {
            $this->lastResponseHeaders = $this->curl->getResponseHeaders();
            $this->lastResponse = $this->curl->getResponseBody();
        }
        $response = $this->curl->getResponseBody();
        // check if we do have a proper soap status code (if not soapfault)
//        // TODO
//        $responseStatusCode = $this->curl->getResponseStatusCode();
//        if ($responseStatusCode >= 400) {
//            $isError = 0;
//            $response = trim($response);
//            if (strlen($response) == 0) {
//                $isError = 1;
//            } else {
//                $contentType = $this->curl->getResponseContentType();
//                if ($contentType != 'application/soap+xml'
//                    && $contentType != 'application/soap+xml') {
//                    if (strncmp($response , "<?xml", 5)) {
//                        $isError = 1;
//                    }
//                }
//            }
//            if ($isError == 1) {
//                throw new \SoapFault('HTTP', $this->curl->getResponseStatusMessage());
//            }
//        } elseif ($responseStatusCode != 200 && $responseStatusCode != 202) {
//            $dom = new \DOMDocument('1.0');
//            $dom->loadXML($response);
//            if ($dom->getElementsByTagNameNS($dom->documentElement->namespaceURI, 'Fault')->length == 0) {
//                throw new \SoapFault('HTTP', 'HTTP response status must be 200 or 202');
//            }
//        }
        return $response;
    }

   /**
     * Custom request method to be able to modify the SOAP messages.
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way 0|1
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        // http request
        $response = $this->__doHttpRequest($request, $location, $action);
        // return SOAP response to ext/soap
        return $response;
    }

    /**
     * Get last request HTTP headers.
     *
     * @return string
     */
    public function __getLastRequestHeaders()
    {
        return $this->lastRequestHeaders;
    }

    /**
     * Get last request HTTP body.
     *
     * @return string
     */
    public function __getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Get last response HTTP headers.
     *
     * @return string
     */
    public function __getLastResponseHeaders()
    {
        return $this->lastResponseHeaders;
    }

    /**
     * Get last response HTTP body.
     *
     * @return string
     */
    public function __getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Downloads WSDL files with cURL. Uses all SoapClient options for
     * authentication. Uses the WSDL_CACHE_* constants and the 'soap.wsdl_*'
     * ini settings. Does only file caching as SoapClient only supports a file
     * name parameter.
     *
     * @param string               $wsdl    WSDL file
     * @param array(string=>mixed) $options Options array
     * @return string
     */
    private function loadWsdl($wsdl, array $options)
    {
        // option to resolve xsd includes
        $resolveXsdIncludes = true;
        if (isset($options['resolve_xsd_includes']))
        {
            $resolveXsdIncludes = $options['resolve_xsd_includes'];
        }
        // option to enable cache
        $wsdlCache = WSDL_CACHE_DISK;
        if (isset($options['cache_wsdl']))
        {
            $wsdlCache = $options['cache_wsdl'];
        }
        $wsdlDownloader = new WsdlDownloader($this->curl, $resolveXsdIncludes, $wsdlCache);
        try {
            $cacheFileName = $wsdlDownloader->download($wsdl);
        } catch (\RuntimeException $e) {
            throw new \SoapFault('WSDL', "SOAP-ERROR: Parsing WSDL: Couldn't load from '" . $wsdl . "' : failed to load external entity \"" . $wsdl . "\"");
        }
        return $cacheFileName;
    }
}