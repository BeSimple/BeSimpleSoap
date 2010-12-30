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
use Bundle\WebServiceBundle\Soap\SoapHeader;

use Bundle\WebServiceBundle\ServiceBinding\ServiceBinder;

use Bundle\WebServiceBundle\Converter\ConverterRepository;

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
     * @var \Bundle\WebServiceBundle\ServiceBinding\ServiceBinder
     */
    protected $serviceBinder;

    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $kernel;

    public function __construct(ServiceBinder $serviceBinder, ConverterRepository $converterRepository, HttpKernelInterface $kernel)
    {
        $this->serviceBinder = $serviceBinder;

        $this->soapServer = new \SoapServer(
            $this->serviceBinder->getSerializedServiceDefinition(),
            array(
                'classmap' => $this->serviceBinder->getSoapServerClassmap(),
            	'typemap'  => $converterRepository->toSoapServerTypemap($this),
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            )
        );
        $this->soapServer->setObject($this);

        $this->kernel = $kernel;
    }

    public function getRequest()
    {
        return $this->soapRequest;
    }

    public function getResponse()
    {
        return $this->soapResponse;
    }

    public function handle(Request $request = null, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->soapRequest = $this->checkRequest($request);

        ob_start();
        $this->soapServer->handle($this->soapRequest->getSoapMessage());

        $soapResponseContent = ob_get_clean();
        $this->soapResponse->setContent($soapResponseContent);

        return $this->soapResponse;
    }

    /**
     * This method gets called once for every SOAP header the \SoapServer received
     * and afterwards once for the called SOAP operation.
     *
     * @param string $method The SOAP header or SOAP operation name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if($this->serviceBinder->isServiceHeader($method))
        {
            // collect request soap headers
            $this->soapRequest->getSoapHeaders()->add(
                $this->serviceBinder->processServiceHeader($method, $arguments[0])
            );

            return;
        }

        if($this->serviceBinder->isServiceMethod($method))
        {
            $this->soapRequest->attributes->add(
                $this->serviceBinder->processServiceMethodArguments($method, $arguments)
            );

            // delegate to standard http kernel
            $response = $this->kernel->handle($this->soapRequest, self::MASTER_REQUEST, true);

            $this->soapResponse = $this->checkResponse($response);

            // add response soap headers to soap server
            foreach($this->soapResponse->getSoapHeaders() as $header)
            {
                $this->soapServer->addSoapHeader($header->toNativeSoapHeader());
            }

            // return operation return value to soap server
            return $this->serviceBinder->processServiceMethodReturnValue(
                $method,
                $this->soapResponse->getReturnValue()
            );
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
            throw new \InvalidArgumentException();
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
        if($response == null || !is_a($response, __NAMESPACE__ . '\\Soap\\SoapResponse'))
        {
            throw new \InvalidArgumentException();
        }

        return $response;
    }
}