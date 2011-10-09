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
    protected $wsdl;
    protected $options;

    /**
     * @return SoapClientBuilder
     */
    static public function createWithDefaults()
    {
        return parent::createWithDefaults()
            ->withUserAgent('BeSimpleSoap')
        ;
    }

    public function withTrace($trace = true)
    {
        $this->options['trace'] = $trace;

        return $this;
    }

    public function withExceptions($exceptions = true)
    {
        $this->options['exceptions'] = $exceptions;

        return $this;
    }

    public function withUserAgent($userAgent)
    {
        $this->options['user_agent'] = $userAgent;

        return $this;
    }
}