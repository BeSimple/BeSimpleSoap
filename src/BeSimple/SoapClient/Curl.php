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
 * cURL wrapper class for doing HTTP requests that uses the soap class options.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class Curl
{
    /**
     * HTTP User Agent.
     *
     * @var string
     */
    const USER_AGENT = 'PHP-SOAP/\BeSimple\SoapClient';

    /**
     * Curl resource.
     *
     * @var resource
     */
    private $ch;

    /**
     * Maximum number of location headers to follow.
     *
     * @var int
     */
    private $followLocationMaxRedirects;

    /**
     * Request response data.
     *
     * @var string
     */
    private $response;

    /**
     * Constructor.
     *
     * @param array $options                    Options array from SoapClient constructor
     * @param int   $followLocationMaxRedirects Redirection limit for Location header
     */
    public function __construct(array $options = array(), $followLocationMaxRedirects = 10)
    {
        // set the default HTTP user agent
        if (!isset($options['user_agent'])) {
            $options['user_agent'] = self::USER_AGENT;
        }
        $this->followLocationMaxRedirects = $followLocationMaxRedirects;

        // make http request
        $this->ch = curl_init();
        $curlOptions = array(
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FAILONERROR => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HEADER => true,
            CURLOPT_USERAGENT => $options['user_agent'],
            CURLINFO_HEADER_OUT => true,
        );
        curl_setopt_array($this->ch, $curlOptions);
        if (isset($options['curl_ssl_version'])){
            curl_setopt($this->ch, CURLOPT_SSLVERSION, $options['curl_ssl_version']);
        }
        if (isset($options['compression']) && !($options['compression'] & SOAP_COMPRESSION_ACCEPT)) {
            curl_setopt($this->ch, CURLOPT_ENCODING, 'identity');
        }
        if (isset($options['connection_timeout'])) {
            curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $options['connection_timeout']);
        }
        if (isset($options['proxy_host'])) {
            $port = isset($options['proxy_port']) ? $options['proxy_port'] : 8080;
            curl_setopt($this->ch, CURLOPT_PROXY, $options['proxy_host'] . ':' . $port);
        }
        if (isset($options['proxy_user'])) {
            curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $options['proxy_user'] . ':' . $options['proxy_password']);
        }
        if (isset($options['login'])) {
            curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($this->ch, CURLOPT_USERPWD, $options['login'].':'.$options['password']);
        }
        if (isset($options['local_cert'])) {
            curl_setopt($this->ch, CURLOPT_SSLCERT, $options['local_cert']);
            curl_setopt($this->ch, CURLOPT_SSLCERTPASSWD, $options['passphrase']);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * Execute HTTP request.
     * Returns true if request was successfull.
     *
     * @param string $location       HTTP location
     * @param string $request        Request body
     * @param array  $requestHeaders Request header strings
     *
     * @return bool
     */
    public function exec($location, $request = null, $requestHeaders = array())
    {
        curl_setopt($this->ch, CURLOPT_URL, $location);

        if (!is_null($request)) {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $request);
        }

        if (count($requestHeaders) > 0) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $requestHeaders);
        }

        $this->response = $this->execManualRedirect();

        return ($this->response === false) ? false : true;
    }

    /**
     * Custom curl_exec wrapper that allows to follow redirects when specific
     * http response code is set. SOAP only allows 307.
     *
     * @param int $redirects Current redirection count
     *
     * @return mixed
     */
    private function execManualRedirect($redirects = 0)
    {
        if ($redirects > $this->followLocationMaxRedirects) {

            // TODO Redirection limit reached, aborting
            return false;
        }
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($this->ch);
        $httpResponseCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if ($httpResponseCode == 307) {
            $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $headerSize);
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = trim(array_pop($matches));
            // @parse_url to suppress E_WARNING for invalid urls
            if (($url = @parse_url($url)) !== false) {
                $lastUrl = parse_url(curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL));
                if (!isset($url['scheme'])) {
                    $url['scheme'] = $lastUrl['scheme'];
                }
                if (!isset($url['host'])) {
                    $url['host'] = $lastUrl['host'];
                }
                if (!isset($url['path'])) {
                    $url['path'] = $lastUrl['path'];
                }
                $newUrl = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
                curl_setopt($this->ch, CURLOPT_URL, $newUrl);

                return $this->execManualRedirect($redirects++);
            }
        }

        return $response;
    }

    /**
     * Error code mapping from cURL error codes to PHP ext/soap error messages
     * (where applicable)
     *
     * http://curl.haxx.se/libcurl/c/libcurl-errors.html
     *
     * @return array(int=>string)
     */
    protected function getErrorCodeMapping()
    {
        return array(
            1 => 'Unknown protocol. Only http and https are allowed.', //CURLE_UNSUPPORTED_PROTOCOL
            3 => 'Unable to parse URL', //CURLE_URL_MALFORMAT
            5 => 'Could not connect to host', //CURLE_COULDNT_RESOLVE_PROXY
            6 => 'Could not connect to host', //CURLE_COULDNT_RESOLVE_HOST
            7 => 'Could not connect to host', //CURLE_COULDNT_CONNECT
            9 => 'Could not connect to host', //CURLE_REMOTE_ACCESS_DENIED
            28 => 'Failed Sending HTTP SOAP request', //CURLE_OPERATION_TIMEDOUT
            35 => 'Could not connect to host', //CURLE_SSL_CONNECT_ERROR
            41 => 'Can\'t uncompress compressed response', //CURLE_FUNCTION_NOT_FOUND
            51 => 'Could not connect to host', //CURLE_PEER_FAILED_VERIFICATION
            52 => 'Error Fetching http body, No Content-Length, connection closed or chunked data', //CURLE_GOT_NOTHING
            53 => 'SSL support is not available in this build', //CURLE_SSL_ENGINE_NOTFOUND
            54 => 'SSL support is not available in this build', //CURLE_SSL_ENGINE_SETFAILED
            55 => 'Failed Sending HTTP SOAP request', //CURLE_SEND_ERROR
            56 => 'Error Fetching http body, No Content-Length, connection closed or chunked data', //CURLE_RECV_ERROR
            58 => 'Could not connect to host', //CURLE_SSL_CERTPROBLEM
            59 => 'Could not connect to host', //CURLE_SSL_CIPHER
            60 => 'Could not connect to host', //CURLE_SSL_CACERT
            61 => 'Unknown Content-Encoding', //CURLE_BAD_CONTENT_ENCODING
            65 => 'Failed Sending HTTP SOAP request', //CURLE_SEND_FAIL_REWIND
            66 => 'SSL support is not available in this build', //CURLE_SSL_ENGINE_INITFAILED
            67 => 'Could not connect to host', //CURLE_LOGIN_DENIED
            77 => 'Could not connect to host', //CURLE_SSL_CACERT_BADFILE
            80 => 'Error Fetching http body, No Content-Length, connection closed or chunked data', //CURLE_SSL_SHUTDOWN_FAILED
        );
    }

    /**
     * Gets the curl error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $errorCodeMapping = $this->getErrorCodeMapping();
        $errorNumber = curl_errno($this->ch);
        if (isset($errorCodeMapping[$errorNumber])) {

            return $errorCodeMapping[$errorNumber];
        }

        return curl_error($this->ch);
    }

    /**
     * Gets the request headers as a string.
     *
     * @return string
     */
    public function getRequestHeaders()
    {
        return curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
    }

    /**
     * Gets the whole response (including headers) as a string.
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Gets the response body as a string.
     *
     * @return string
     */
    public function getResponseBody()
    {
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);

        return substr($this->response, $headerSize);
    }

    /**
     * Gets the response content type.
     *
     * @return string
     */
    public function getResponseContentType()
    {
        return curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
    }

    /**
     * Gets the response headers as a string.
     *
     * @return string
     */
    public function getResponseHeaders()
    {
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);

        return substr($this->response, 0, $headerSize);
    }

    /**
     * Gets the response http status code.
     *
     * @return string
     */
    public function getResponseStatusCode()
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    /**
     * Gets the response http status message.
     *
     * @return string
     */
    public function getResponseStatusMessage()
    {
        preg_match('/HTTP\/(1\.[0-1]+) ([0-9]{3}) (.*)/', $this->response, $matches);

        return trim(array_pop($matches));
    }
}