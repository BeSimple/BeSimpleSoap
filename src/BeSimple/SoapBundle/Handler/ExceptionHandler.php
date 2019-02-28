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

namespace BeSimple\SoapBundle\Handler;

use BeSimple\SoapServer\Exception\ReceiverSoapFault;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ExceptionHandler
{
    protected $exception;
    protected $details;
    protected $soapFault;

    public function __construct(FlattenException $exception, $details = null)
    {
        $this->exception = $exception;
        $this->details = $details;
    }

    public function setSoapFault(\SoapFault $soapFault)
    {
        $this->soapFault = $soapFault;
    }

    public function __call($method, $arguments)
    {
        if (isset($this->soapFault)) {
            throw $this->soapFault;
        }

        $code = $this->exception->getStatusCode();

        throw new ReceiverSoapFault(
            isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
            null,
            $this->details
        );
    }
}
