<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceBinding;

use BeSimple\SoapBundle\ServiceDefinition\Header;
use BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition;
use BeSimple\SoapBundle\Soap\SoapHeader;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class ServiceBinder
{
    /**
     * @var \BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition
     */
    private $definition;

    /**
     * @var \BeSimple\SoapBundle\ServiceBinding\MessageBinderInterface
     */
    private $requestHeaderMessageBinder;

    /**
     * @var \BeSimple\SoapBundle\ServiceBinding\MessageBinderInterface
     */
    private $requestMessageBinder;

    /**
     * @var \BeSimple\SoapBundle\ServiceBinding\MessageBinderInterface
     */
    private $responseMessageBinder;

    /**
     * @param ServiceDefinition $definition
     * @param MessageBinderInterface $requestHeaderMessageBinder
     * @param MessageBinderInterface $requestMessageBinder
     * @param MessageBinderInterface $responseMessageBinder
     */
    public function __construct(ServiceDefinition $definition, MessageBinderInterface $requestHeaderMessageBinder, MessageBinderInterface $requestMessageBinder, MessageBinderInterface $responseMessageBinder) {
        $this->definition = $definition;

        $this->requestHeaderMessageBinder = $requestHeaderMessageBinder;
        $this->requestMessageBinder       = $requestMessageBinder;

        $this->responseMessageBinder = $responseMessageBinder;
    }

    /**
     * @param string $method
     * @param string $header
     *
     * @return boolean
     */
    public function isServiceHeader($method, $header)
    {
        return $this->definition->getMethods()->get($method)->getHeaders()->has($header);
    }

    /**
     * @param $string
     *
     * @return boolean
     */
    public function isServiceMethod($method)
    {
        return $this->definition->getMethods()->has($method);
    }

    /**
     * @param string $method
     * @param string $header
     * @param mixed $data
     *
     * @return SoapHeader
     */
    public function processServiceHeader($method, $header, $data)
    {
        $methodDefinition = $this->definition->getMethods()->get($method);
        $headerDefinition = $methodDefinition->getHeaders()->get($header);

        $this->requestHeaderMessageBinder->setHeader($header);
        $data = $this->requestHeaderMessageBinder->processMessage($methodDefinition, $data, $this->definition->getDefinitionComplexTypes());

        return new SoapHeader($this->definition->getNamespace(), $headerDefinition->getName(), $data);
    }

    /**
     * @param string $method
     * @param array $arguments
     *
     * @return array
     */
    public function processServiceMethodArguments($method, $arguments)
    {
        $methodDefinition = $this->definition->getMethods()->get($method);

        return array_merge(
            array('_controller' => $methodDefinition->getController()),
            $this->requestMessageBinder->processMessage($methodDefinition, $arguments, $this->definition->getDefinitionComplexTypes())
        );
    }

    /**
     * @param string $name
     * @param mixed
     *
     * @return mixed
     */
    public function processServiceMethodReturnValue($name, $return)
    {
        $methodDefinition = $this->definition->getMethods()->get($name);

        return $this->responseMessageBinder->processMessage($methodDefinition, $return, $this->definition->getDefinitionComplexTypes());
    }
}