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
use BeSimple\SoapBundle\Util\QName;

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
    private $requestMessageBinder;

    /**
     * @var \BeSimple\SoapBundle\ServiceBinding\MessageBinderInterface
     */
    private $responseMessageBinder;

    public function __construct(ServiceDefinition $definition, MessageBinderInterface $requestMessageBinder, MessageBinderInterface $responseMessageBinder) {
        $this->definition            = $definition;
        $this->requestMessageBinder  = $requestMessageBinder;
        $this->responseMessageBinder = $responseMessageBinder;
    }

    public function isServiceHeader($name)
    {
        return $this->definition->getHeaders()->has($name);
    }

    public function isServiceMethod($name)
    {
        return $this->definition->getMethods()->has($name);
    }

    public function processServiceHeader($name, $data)
    {
        $headerDefinition = $this->definition->getHeaders()->get($name);

        return $this->createSoapHeader($headerDefinition, $data);
    }

    public function processServiceMethodArguments($name, $arguments)
    {
        $methodDefinition = $this->definition->getMethods()->get($name);

        return array_merge(
            array('_controller' => $methodDefinition->getController()),
            $this->requestMessageBinder->processMessage($methodDefinition, $arguments, $this->definition->getDefinitionComplexTypes())
        );
    }

    public function processServiceMethodReturnValue($name, $return)
    {
        $methodDefinition = $this->definition->getMethods()->get($name);

        return $this->responseMessageBinder->processMessage($methodDefinition, $return, $this->definition->getDefinitionComplexTypes());
    }

    protected function createSoapHeader(Header $headerDefinition, $data)
    {
        $qname = QName::fromPackedQName($headerDefinition->getType()->getXmlType());

        return new SoapHeader($qname->getNamespace(), $qname->getName(), $data);
    }
}