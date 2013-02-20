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

namespace BeSimple\SoapBundle\EventListener;

use BeSimple\SoapBundle\Soap\SoapRequest;
use BeSimple\SoapBundle\Soap\SoapResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * SoapResponseListener.
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class SoapResponseListener
{
    /**
     * @var SoapResponse
     */
    protected $response;

    /**
     * Constructor.
     *
     * @param SoapResponse $response The SoapResponse instance
     */
    public function __construct(SoapResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Set the controller result in SoapResponse.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        if (!$request instanceof SoapRequest) {
            return;
        }

        $this->response->setReturnValue($event->getControllerResult());
        $event->setResponse($this->response);
    }
}
