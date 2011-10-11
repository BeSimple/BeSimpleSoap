<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapClient;

use BeSimple\SoapCommon\AbstractSoapBuilder;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapClientBuilder extends AbstractSoapBuilder
{
    protected $soapOptionAuthentication = array();

    /**
     * @return SoapClientBuilder
     */
    static public function createWithDefaults()
    {
        return parent::createWithDefaults()
            ->withUserAgent('BeSimpleSoap')
        ;
    }

    /**
     * @return SoapClient
     */
    public function build()
    {
        $this->validateOptions();

        return new SoapClient($this->wsdl, $this->getSoapOptions());
    }

    public function getSoapOptions()
    {
        return parent::getSoapOptions() + $this->soapOptionAuthentication;
    }

    /**
     * @return SoapClientBuilder
     */
    public function withTrace($trace = true)
    {
        $this->soapOptions['trace'] = $trace;

        return $this;
    }

    /**
     * @return SoapClientBuilder
     */
    public function withExceptions($exceptions = true)
    {
        $this->soapOptions['exceptions'] = $exceptions;

        return $this;
    }

    /**
     * @return SoapClientBuilder
     */
    public function withUserAgent($userAgent)
    {
        $this->soapOptions['user_agent'] = $userAgent;

        return $this;
    }

    /**
     * @return SoapClientBuilder
     */
    public function withBasicAuthentication($username, $password)
    {
        $this->soapOptionAuthentication = array(
            'authentication' => SOAP_AUTHENTICATION_BASIC,
            'login'          => $username,
            'password'       => $password,
        );

        return $this;
    }

    /**
     * @return SoapClientBuilder
     */
    public function withDigestAuthentication($certificate, $passphrase = null)
    {
        $this->soapOptionAuthentication = array(
            'authentication' => SOAP_AUTHENTICATION_DIGEST,
            'local_cert'     => $certificate,
        );

        if ($passphrase) {
            $this->soapOptionAuthentication['passphrase'] = $passphrase;
        }

        return $this;
    }

    public function withProxy($host, $port, $username = null, $password = null)
    {
        $this->soapOptions['proxy_host'] = $host;
        $this->soapOptions['proxy_port'] = $port;

        if ($username) {
            $this->soapOptions['proxy_login']    = $username;
            $this->soapOptions['proxy_password'] = $password;
        }

        return $this;
    }

    protected function validateOptions()
    {
        $this->validateWsdl();
    }
}