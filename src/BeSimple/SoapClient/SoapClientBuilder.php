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
 */
class SoapClientBuilder extends AbstractSoapBuilder
{
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

        return new SoapClient($this->optionWsdl, $this->options);
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

    protected function validateOptions()
    {
        $this->validateWsdl();
    }
}