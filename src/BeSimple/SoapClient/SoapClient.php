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

use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\Converter\MtomTypeConverter;
use BeSimple\SoapCommon\Converter\SwaTypeConverter;

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
     * @var bool
     */
    protected $tracingEnabled = false;

    /**
     * cURL instance.
     *
     * @var \BeSimple\SoapClient\Curl
     */
    protected $curl = null;

    /**
     * request headers.
     *
     * @var array
     */
    protected $requestHeaders = array();

    /**
     * Last request headers.
     *
     * @var string
     */
    protected $lastRequestHeaders = '';

    /**
     * Last request.
     *
     * @var string
     */
    protected $lastRequest = '';

    /**
     * Last request URI.
     *
     * @var string
     */
    protected $lastRequestUri = '';

    /**
     * Last response headers.
     *
     * @var string
     */
    protected $lastResponseHeaders = '';

    /**
     * Last response code.
     *
     * @var string
     */
    protected $lastResponseCode = '';

    /**
     * Last response.
     *
     * @var string
     */
    protected $lastResponse = '';

    /**
     * Soap kernel.
     *
     * @var \BeSimple\SoapClient\SoapKernel
     */
    protected $soapKernel = null;

    protected $authType = Curl::AUTH_TYPE_NONE;

    /**
     * Constructor.
     *
     * @param string               $wsdl    WSDL file
     * @param array(string=>mixed) $options Options array
     *
     * @throws \SoapFault
     */
    public function __construct($wsdl, array $options = array())
    {
        // tracing enabled: store last request/response header and body
        if (isset($options['trace']) && true === $options['trace']) {
            $this->tracingEnabled = true;
        }
        // store SOAP version
        if (isset($options['soap_version'])) {
            $this->soapVersion = $options['soap_version'];
        }

        $this->curl = new Curl($options);

        if (isset($options['extra_options'])) {
            unset($options['extra_options']);
        }

        $wsdlFile = $this->loadWsdl($wsdl, $options);
        // TODO $wsdlHandler = new WsdlHandler($wsdlFile, $this->soapVersion);
        $this->soapKernel = new SoapKernel();
        // set up type converter and mime filter
        $this->configureMime($options);
        // we want the exceptions option to be set
        $options['exceptions'] = true;
        // disable obsolete trace option for native SoapClient as we need to do our own tracing anyways
        $options['trace'] = false;
        // disable WSDL caching as we handle WSDL caching for remote URLs ourself
        $options['cache_wsdl'] = WSDL_CACHE_NONE;

        try {
            parent::__construct($wsdlFile, $options);
        } catch (\SoapFault $soapFault) {
            // Discard cached WSDL file if there's a problem with it
            if ('WSDL' === $soapFault->faultcode) {
                unlink($wsdlFile);
            }

            throw $soapFault;
        }
    }

    /**
     * Perform HTTP request with cURL.
     *
     * @param SoapRequest $soapRequest SoapRequest object
     *
     * @return SoapResponse
     */
    protected function __doHttpRequest(SoapRequest $soapRequest)
    {
        // HTTP headers
        $soapVersion = $soapRequest->getVersion();
        $soapAction = $soapRequest->getAction();
        if (SOAP_1_1 == $soapVersion) {
            $staticallyAddedHeaders = array(
                'Content-Type:'.$soapRequest->getContentType(),
                'SOAPAction: "'.$soapAction.'"',
            );
        } else {
            $staticallyAddedHeaders = array(
               'Content-Type:'.$soapRequest->getContentType().'; action="'.$soapAction.'"',
            );
        }

        $location = $soapRequest->getLocation();
        $this->lastRequestUri = $location;
        $content = $soapRequest->getContent();
        $staticallyAddedHeaders = $this->filterRequestHeaders($soapRequest, $staticallyAddedHeaders);
        $options = $this->filterRequestOptions($soapRequest);
        $flattenedHttpHeaders = $this->getRequestHeadersForCurl(); // flatten key/value pair array into single string array
        $flattenedHttpHeaders = array_merge($flattenedHttpHeaders, $staticallyAddedHeaders); //add statically added headers to the headers passed in

        // execute HTTP request with cURL
        $responseSuccessfull = $this->curl->exec(
            $location,
            $content,
            $flattenedHttpHeaders,
            $options
        );

        // tracing enabled: store last request header and body
        if (true === $this->tracingEnabled) {
            $this->lastRequestHeaders .= 'POST '.$soapRequest->getLocation()."\n";
            $this->lastRequestHeaders .= 'SOAPAction: '.$soapRequest->getAction()."\n";
            $this->lastRequestHeaders .= 'SOAPVersion: '.$soapRequest->getVersion()."\n";
            $this->lastRequestHeaders .= 'Content-Type: '.$soapRequest->getContentType()."\n";
            $this->lastRequestHeaders .= $this->curl->getRequestHeaders();
            $this->lastRequest = $soapRequest->getContent();
        }
        // in case of an error while making the http request throw a soapFault
        if (false === $responseSuccessfull) {
            // get error message from curl
            $faultstring = $this->curl->getErrorMessage();
            throw new \SoapFault('HTTP', $faultstring);
        }
        // tracing enabled: store last response header and body
        if (true === $this->tracingEnabled) {
            $this->lastResponseHeaders = $this->curl->getResponseHeaders();
            $this->lastResponse = $this->curl->getResponseBody();
            $this->lastResponseCode = $this->curl->getResponseStatusCode();
        }
        // wrap response data in SoapResponse object
        $soapResponse = SoapResponse::create(
            $this->curl->getResponseBody(),
            $soapRequest->getLocation(),
            $soapRequest->getAction(),
            $soapRequest->getVersion(),
            $this->curl->getResponseContentType()
        );

        return $soapResponse;
    }

    /**
     * Custom request method to be able to modify the SOAP messages.
     * $oneWay parameter is not used at the moment.
     *
     * @param string $request  Request string
     * @param string $location Location
     * @param string $action   SOAP action
     * @param int    $version  SOAP version
     * @param int    $oneWay   0|1
     *
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $oneWay = 0)
    {
        // wrap request data in SoapRequest object
        $soapRequest = SoapRequest::create($request, $location, $action, $version);

        // do actual SOAP request
        $soapResponse = $this->__doRequest2($soapRequest);

        // return SOAP response to ext/soap
        return $soapResponse->getContent();
    }

    /**
     * Runs the currently registered request filters on the request, performs
     * the HTTP request and runs the response filters.
     *
     * @param SoapRequest $soapRequest SOAP request object
     *
     * @return SoapResponse
     */
    protected function __doRequest2(SoapRequest $soapRequest)
    {
        // run SoapKernel on SoapRequest
        $this->soapKernel->filterRequest($soapRequest);

        // perform HTTP request with cURL
        $soapResponse = $this->__doHttpRequest($soapRequest);

        // run SoapKernel on SoapResponse
        $this->soapKernel->filterResponse($soapResponse);

        return $soapResponse;
    }

    /**
     * Filters HTTP headers which will be sent.
     *
     * @param SoapRequest $soapRequest SOAP request object
     * @param array       $headers     An array of HTTP headers
     *
     * @return array
     */
    protected function filterRequestHeaders(SoapRequest $soapRequest, array $headers)
    {
        if (Curl::AUTH_TYPE_BASIC === $this->authType && isset($this->_login) && isset($this->_password)) {
            $authToken = base64_encode(sprintf('%s:%s', $this->_login, $this->_password));
            $headers[] = sprintf('Authorization: Basic %s', $authToken);
        }

        return $headers;
    }

    /**
     * Adds additional cURL options for the request.
     *
     * @param SoapRequest $soapRequest SOAP request object
     *
     * @return array
     */
    protected function filterRequestOptions(SoapRequest $soapRequest)
    {
        return array();
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
     * Get request HTTP headers.
     *
     * @return string
     */
    public function __getRequestHeaders()
    {
        return $this->requestHeaders;
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
     * Get last request HTTP URI.
     *
     * @return string
     */
    public function __getLastRequestUri()
    {
        return $this->lastRequestUri;
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
     * Get last response HTTP code.
     *
     * @return int
     */
    public function __getLastResponseCode()
    {
        return $this->lastResponseCode;
    }

    /**
     * Get SoapKernel instance.
     *
     * @return \BeSimple\SoapClient\SoapKernel
     */
    public function getSoapKernel()
    {
        return $this->soapKernel;
    }

    /**
     * Configure filter and type converter for SwA/MTOM.
     *
     * @param array &$options SOAP constructor options array
     */
    protected function configureMime(array &$options)
    {
        if (isset($options['attachment_type']) && Helper::ATTACHMENTS_TYPE_BASE64 !== $options['attachment_type']) {
            // register mime filter in SoapKernel
            $mimeFilter = new MimeFilter($options['attachment_type']);
            $this->soapKernel->registerFilter($mimeFilter);
            // configure type converter
            if (Helper::ATTACHMENTS_TYPE_SWA === $options['attachment_type']) {
                $converter = new SwaTypeConverter();
                $converter->setKernel($this->soapKernel);
            } elseif (Helper::ATTACHMENTS_TYPE_MTOM === $options['attachment_type']) {
                $xmlMimeFilter = new XmlMimeFilter($options['attachment_type']);
                $this->soapKernel->registerFilter($xmlMimeFilter);
                $converter = new MtomTypeConverter();
                $converter->setKernel($this->soapKernel);
            }
            // configure typemap
            if (!isset($options['typemap'])) {
                $options['typemap'] = array();
            }
            $options['typemap'][] = array(
                'type_name' => $converter->getTypeName(),
                'type_ns' => $converter->getTypeNamespace(),
                'from_xml' => function ($input) use ($converter) {
                    return $converter->convertXmlToPhp($input);
                },
                'to_xml' => function ($input) use ($converter) {
                    return $converter->convertPhpToXml($input);
                },
            );
        }
    }

    /**
     * Downloads WSDL files with cURL. Uses all SoapClient options for
     * authentication. Uses the WSDL_CACHE_* constants and the 'soap.wsdl_*'
     * ini settings. Does only file caching as SoapClient only supports a file
     * name parameter.
     *
     * @param string               $wsdl    WSDL file
     * @param array(string=>mixed) $options Options array
     *
     * @return string
     */
    protected function loadWsdl($wsdl, array $options)
    {
        // option to resolve wsdl/xsd includes
        $resolveRemoteIncludes = true;
        if (isset($options['resolve_wsdl_remote_includes'])) {
            $resolveRemoteIncludes = $options['resolve_wsdl_remote_includes'];
        }
        // option to enable cache
        $wsdlCache = WSDL_CACHE_DISK;
        if (isset($options['cache_wsdl'])) {
            $wsdlCache = $options['cache_wsdl'];
        }
        $wsdlDownloader = new WsdlDownloader($this->curl, $resolveRemoteIncludes, $wsdlCache);
        try {
            $cacheFileName = $wsdlDownloader->download($wsdl);
        } catch (\RuntimeException $e) {
            throw new \SoapFault('WSDL', "SOAP-ERROR: Parsing WSDL: Couldn't load from '".$wsdl."' : failed to load external entity \"".$wsdl.'"');
        }

        return $cacheFileName;
    }

    /**
     * @param $value
     */
    public function setExecutionTimeout($value)
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('Expected integer as an input');
        }
        $this->curl->setOption(CURLOPT_TIMEOUT, $value);
    }

    /**
     * @param array $headers
     */
    public function setRequestHeaders($headers)
    {
        $this->requestHeaders = $headers;
    }

    public function getRequestHeadersForCurl()
    {
        $requestHeadersStringArray = array();
        foreach ($this->requestHeaders as $key => $value) {
            $requestHeadersStringArray[] = $key.': '.$value;
        }

        return $requestHeadersStringArray;
    }

    public function getAuthType()
    {
        return $this->authType;
    }

    public function setAuthType($authType)
    {
        $this->authType = $authType;
    }
}
