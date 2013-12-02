<?php

namespace BeSimple\SoapBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestFormatListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->setFormat('wsdl', 'application/wsdl+xml');
        $event->getRequest()->setFormat('soap', 'application/soap+xml');
    }
}
