<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\HttpKernelInterface;

use Bundle\WebServiceBundle\Soap\SoapRequest;
use Bundle\WebServiceBundle\Soap\SoapResponse;

use Bundle\WebServiceBundle\Util\OutputBuffer;
use Bundle\WebServiceBundle\Util\String;

/**
 * SoapKernel converts a SoapRequest to a SoapResponse. It uses PHP's SoapServer for SOAP message
 * handling. The logic for every service method is implemented in a Symfony controller. The controller
 * to use for a specific service method is defined in the ServiceDefinition. The controller is invoked
 * by Symfony's HttpKernel implementation.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapKernel implements HttpKernelInterface
{
    /**
     * @var \SoapServer
     */
    protected $soapServer;

    /**
     * @var \Bundle\WebServiceBundle\Soap\SoapRequest
     */
    protected $soapRequest;

    /**
     * @var \Bundle\WebServiceBundle\Soap\SoapResponse
     */
    protected $soapResponse;

    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $kernel;

    public function __construct(\SoapServer $server, HttpKernelInterface $kernel)
    {
        $this->soapServer = $server;
        $this->soapServer->setObject($this);

        $this->kernel = $kernel;
    }

    public function getRequest()
    {
        return $this->soapRequest;
    }

    public function handle(Request $request = null, $type = self::MASTER_REQUEST, $raw = false)
    {
        $this->soapRequest = $this->checkRequest($request);

        $this->soapResponse->setContent(OutputBuffer::get(
            function() use($this)
            {
                $this->soapServer->handle($this->soapRequest->getRawContent());
            }
        ));

        return $this->soapResponse;
    }

    public function __call($method, $arguments)
    {
        if($this->isSoapHeaderCallback($method))
        {
            // $this->soapRequest->addSoapHeader(null);
        }
        else
        {
            // TODO: set _controller attribute of request
            $this->soapRequest->attributes->set('_controller', $method);

            $response = $this->kernel->handle($this->soapRequest, self::MASTER_REQUEST, true);

            $this->soapResponse = $this->checkResponse($response);

            foreach($this->soapResponse->getSoapHeaders() as $header)
            {
                $this->soapServer->addSoapHeader($header);
            }

            return $this->soapResponse->getReturnValue();
        }
    }

    /**
     * Checks the given Request, that it is a SoapRequest. If the request is null a new
     * SoapRequest is created.
     *
     * @param Request $request A request to check
     *
     * @return SoapRequest A valid SoapRequest
     *
     * @throws InvalidArgumentException if the given Request is not a SoapRequest
     */
    protected function checkRequest(Request $request)
    {
        if($request == null)
        {
            $request = new SoapRequest();
        }

        if(!is_a($request, __NAMESPACE__ . '\\Soap\\SoapRequest'))
        {
            throw new InvalidArgumentException();
        }

        return $request;
    }

    /**
     * Checks the given Response, that it is a SoapResponse.
     *
     * @param Response $response A response to check
     *
     * @return SoapResponse A valid SoapResponse
     *
     * @throws InvalidArgumentException if the given Response is null or not a SoapResponse
     */
    protected function checkResponse(Response $response)
    {
        if($response == null || !is_a($request, __NAMESPACE__ . '\\Soap\\SoapResponse'))
        {
            throw new InvalidArgumentException();
        }

        return $response;
    }

    protected function isSoapHeaderCallback($method)
    {
        return false; //String::endsWith($method, 'Header');
    }
}