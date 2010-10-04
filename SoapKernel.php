<?php

namespace Bundle\WebServiceBundle;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\HttpKernel\HttpKernelInterface;


class SoapKernel extends ContainerAware implements HttpKernelInterface
{
    public function getRequest()
    {
        return null;
    }

    public function handle(Request:: $request = null, $type = self::MASTER_REQUEST, $raw = false)
    {
        $this->container->getSymfonyHttpKernelService()->handle($request, $type, $raw);
    }
}