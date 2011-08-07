<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Controller;

use BeSimple\SoapBundle\Soap\SoapRequest;
use BeSimple\SoapBundle\Soap\SoapResponse;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapWebServiceController extends ContainerAware
{
    /**
     * @var \SoapServer
     */
    protected $soapServer;

    /**
     * @var \BeSimple\SoapBundle\Soap\SoapRequest
     */
    protected $soapRequest;

    /**
     * @var \BeSimple\SoapBundle\Soap\SoapResponse
     */
    protected $soapResponse;

    /**
     * @var \BeSimple\SoapBundle\ServiceBinding\ServiceBinder
     */
    protected $serviceBinder;

    /**
     * @return \BeSimple\SoapBundle\Soap\SoapResponse
     */
    public function callAction($webservice)
    {
        $webServiceContext = $this->getWebServiceContext($webservice);
        $this->serviceBinder = $webServiceContext->getServiceBinder();

        $this->soapRequest = SoapRequest::createFromHttpRequest($this->container->get('request'));
        $this->soapServer  = $webServiceContext->getServerFactory()->create($this->soapRequest, $this->soapResponse);

        $this->soapServer->setObject($this);

        ob_start();
        $this->soapServer->handle($this->soapRequest->getSoapMessage());
        $this->soapResponse->setContent(ob_get_clean());

        return $this->soapResponse;
    }

    /**
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function definitionAction($webservice)
    {
        $webServiceContext = $this->getWebServiceContext($webservice);
        $request           = $this->container->get('request');

        if ($request->query->has('wsdl') || $request->query->has('WSDL')) {
            $endpoint = $this->container->get('router')->generate('_webservice_call', array('webservice' => $webservice), true);

            $response = new Response($webServiceContext->getWsdlFileContent($endpoint));
            $response->headers->set('Content-Type', 'application/wsdl+xml');
        } else {
            // TODO: replace with better representation
            $response = new Response($webServiceContext->getWsdlFileContent());
            $response->headers->set('Content-Type', 'text/xml');
        }

        return $response;
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
        if ($this->serviceBinder->isServiceHeader($method)) {
            // collect request soap headers
            $this->soapRequest->getSoapHeaders()->add(
                $this->serviceBinder->processServiceHeader($method, $arguments[0])
            );

            return;
        }

        if ($this->serviceBinder->isServiceMethod($method)) {
            $this->soapRequest->attributes->add(
                $this->serviceBinder->processServiceMethodArguments($method, $arguments)
            );

            // forward to controller
            $response = $this->container->get('http_kernel')->handle($this->soapRequest, HttpKernelInterface::SUB_REQUEST, false);

            $this->soapResponse = $this->checkResponse($response);

            // add response soap headers to soap server
            foreach ($this->soapResponse->getSoapHeaders() as $header) {
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
     * Checks that the given Response is a SoapResponse.
     *
     * @param Response $response A response to check
     *
     * @return SoapResponse A valid SoapResponse
     *
     * @throws InvalidArgumentException if the given Response is null or not a SoapResponse
     */
    protected function checkResponse(Response $response)
    {
        if (null === $response || !$response instanceof SoapResponse) {
            throw new \InvalidArgumentException();
        }

        return $response;
    }

    /**
     * @return \BeSimple\SoapBundle\Soap\SoapRequest
     */
    public function getRequest()
    {
        return $this->soapRequest;
    }

    /**
     * @return \BeSimple\SoapBundle\Soap\SoapResponse
     */
    public function getResponse()
    {
        return $this->soapResponse;
    }

    private function getWebServiceContext($webservice)
    {
        if(!$this->container->has('besimple.soap.context.'.$webservice))
        {
            throw new NotFoundHttpException(sprintf('No webservice with name "%s" found.', $webservice));
        }
        return $this->container->get('besimple.soap.context.'.$webservice);
    }
}
