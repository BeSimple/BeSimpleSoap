<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Dumper;

use Bundle\WebServiceBundle\ServiceDefinition\Method;
use Bundle\WebServiceBundle\ServiceDefinition\ServiceDefinition;

use Bundle\WebServiceBundle\Util\Assert;

use Zend\Soap\Wsdl;


/**
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class WsdlFileDumper extends FileDumper
{
    private $namespace;

    public function __construct($file, $namespace)
    {
        parent::__construct($file);

        Assert::thatArgumentNotNull('namespace', $namespace);

        $this->namespace = $namespace;
    }

    public function dumpServiceDefinition(ServiceDefinition $definition)
    {
        Assert::thatArgumentNotNull('definition', $definition);

        $wsdl = new Wsdl($definition->getName(), $this->namespace);

        $port = $wsdl->addPortType($this->getPortTypeName($definition));
        $binding = $wsdl->addBinding($this->getBindingName($definition), $this->getPortTypeName($definition));

        $wsdl->addSoapBinding($binding, 'document');
        $wsdl->addService($this->getServiceName($definition), $this->getPortTypeName($definition), $this->getBindingName($definition), 'http://localhost/service/');

        foreach($definition->getMethods() as $method)
        {
            $requestParts = array();
            $responseParts = array();

            foreach($method->getArguments() as $argument)
            {
                $requestParts[$argument->getName()] = $wsdl->getType($argument->getType()->getPhpType());
            }

            $responseParts['return'] = $wsdl->getType($method->getReturn()->getPhpType());

            $wsdl->addMessage($this->getRequestMessageName($method), $requestParts);
            $wsdl->addMessage($this->getResponseMessageName($method), $responseParts);

            $portOperation = $wsdl->addPortOperation($port, $method->getName(), $this->getRequestMessageName($method), $this->getResponseMessageName($method));
            $portOperation->setAttribute('parameterOrder', implode(' ', array_keys($requestParts)));

            $bindingInput = array(
                'parts' => implode(' ', array_keys($requestParts)),
                'use' => 'literal',
                'namespace' => $this->namespace,
                'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/',
            );
            $bindingOutput = array(
                'parts' => implode(' ', array_keys($responseParts)),
                'use' => 'literal',
                'namespace' => $this->namespace,
                'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/',
            );

            $bindingOperation = $wsdl->addBindingOperation($binding, $method->getName(), $bindingInput, $bindingOutput);
            $wsdl->addSoapOperation($bindingOperation, $this->getSoapOperationName($method));
        }

        $wsdl->dump($this->file);
    }

    protected function getPortTypeName(ServiceDefinition $definition)
    {
        return $definition->getName() . 'PortType';
    }

    protected function getBindingName(ServiceDefinition $definition)
    {
        return $definition->getName() . 'Binding';
    }

    protected function getServiceName(ServiceDefinition $definition)
    {
        return $definition->getName() . 'Service';
    }

    protected function getRequestMessageName(Method $method)
    {
        return $method->getName() . 'Request';
    }

    protected function getResponseMessageName(Method $method)
    {
        return $method->getName() . 'Response';
    }

    protected function getSoapOperationName(Method $method)
    {
        return $this->namespace . $method->getName();
    }
}
