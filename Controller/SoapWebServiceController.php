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
     * @var array
     */
    private $headers = array();

    /**
     * @return \BeSimple\SoapBundle\Soap\SoapResponse
     */
    public function callAction($webservice)
    {
        $webServiceContext   = $this->getWebServiceContext($webservice);

        $this->serviceBinder = $webServiceContext->getServiceBinder();

        $this->soapRequest = SoapRequest::createFromHttpRequest($this->container->get('request'));
        $this->soapServer  = $webServiceContext
            ->getServerBuilder()
            ->withHandler($this)
            ->build()
        ;

        ob_start();
        $this->soapServer->handle($this->soapRequest->getSoapMessage());
        $this->getResponse()->setContent(ob_get_clean());

        return $this->getResponse();
    }

    /**
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function definitionAction($webservice)
    {
        $response = new Response($this->getWebServiceContext($webservice)->getWsdlFileContent(
            $this->container->get('router')->generate(
                '_webservice_call',
                array('webservice' => $webservice),
                true
            )
        ));

        $request = $this->container->get('request');
        if ($request->query->has('wsdl') || $request->query->has('WSDL')) {
            $response->headers->set('Content-Type', 'application/wsdl+xml');
        } else {
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
        if ($this->serviceBinder->isServiceMethod($method)) {
            // @TODO Add all SoapHeaders in SoapRequest
            foreach ($this->headers as $name => $value) {
                if ($this->serviceBinder->isServiceHeader($method, $name)) {
                    $this->soapRequest->getSoapHeaders()->add($this->serviceBinder->processServiceHeader($method, $name, $value));
                }
            }
            $this->headers = null;

            $this->soapRequest->attributes->add(
                $this->serviceBinder->processServiceMethodArguments($method, $arguments)
            );

            // forward to controller
            try {
                $response = $this->container->get('http_kernel')->handle($this->soapRequest, HttpKernelInterface::SUB_REQUEST, false);
            } catch (\SoapFault $e) {
                $this->soapResponse = new Response(null, 500);

                throw $e;
            }

            $this->soapResponse = $this->checkResponse($response);

            // add response soap headers to soap server
            foreach ($this->getResponse()->getSoapHeaders() as $header) {
                $this->soapServer->addSoapHeader($header->toNativeSoapHeader());
            }

            // return operation return value to soap server
            return $this->serviceBinder->processServiceMethodReturnValue(
                $method,
                $this->getResponse()->getReturnValue()
            );
        } else {
            // collect request soap headers
            $this->headers[$method] = $arguments[0];
        }
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
        return $this->soapResponse ?: $this->soapResponse = $this->container->get('besimple.soap.response');
    }

    /**
     * Checks that the given Response is a SoapResponse.
     *
     * @param Response $response A response to check
     *
     * @return SoapResponse A valid SoapResponse
     *
     * @throws InvalidArgumentException if the given Response is not an instance of SoapResponse
     */
    protected function checkResponse(Response $response)
    {
        if (!$response instanceof SoapResponse) {
            throw new \InvalidArgumentException('You must return an instance of BeSimple\SoapBundle\Soap\SoapResponse');
        }

        return $response;
    }

    private function getWebServiceContext($webservice)
    {
        if (!$this->container->has('besimple.soap.context.'.$webservice)) {
            throw new NotFoundHttpException(sprintf('No webservice with name "%s" found.', $webservice));
        }

        return $this->container->get('besimple.soap.context.'.$webservice);
    }
}
