<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Dumper;

use BeSimple\SoapBundle\Converter\TypeRepository;
use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapBundle\ServiceDefinition\Type;
use BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition;
use BeSimple\SoapBundle\ServiceDefinition\Loader\AnnotationComplexTypeLoader;
use BeSimple\SoapBundle\Util\Assert;
use BeSimple\SoapBundle\Util\QName;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class WsdlDumper implements DumperInterface
{
    private $loader;
    private $typeRepository;
    private $options;

    private $wsdl;
    private $definition;

    public function __construct(AnnotationComplexTypeLoader $loader, TypeRepository $typeRepository, array $options)
    {
        $this->loader         = $loader;
        $this->typeRepository = $typeRepository;
        $this->options        = $options;
    }

    public function dumpServiceDefinition(ServiceDefinition $definition, $endpoint)
    {
        Assert::thatArgumentNotNull('definition', $definition);

        $this->definition = $definition;
        $this->wsdl       = new Wsdl($this->typeRepository, $definition->getName(), $definition->getNamespace(), new WsdlTypeStrategy($this->loader, $definition));
        $port             = $this->wsdl->addPortType($this->getPortTypeName());
        $binding          = $this->wsdl->addBinding($this->getBindingName(), $this->qualify($this->getPortTypeName()));

        $this->wsdl->addSoapBinding($binding, 'rpc');
        $this->wsdl->addService($this->getServiceName(), $this->getPortName(), $this->qualify($this->getBindingName()), $endpoint);

        foreach ($definition->getMethods() as $method) {
            $requestHeaderParts =
            $requestParts       =
            $responseParts      = array();

            foreach ($method->getHeaders() as $header) {
                $requestHeaderParts[$header->getName()] = $this->wsdl->getType($header->getType()->getPhpType());
            }

            foreach ($method->getArguments() as $argument) {
                $requestParts[$argument->getName()] = $this->wsdl->getType($argument->getType()->getPhpType());
            }

            if ($method->getReturn() !== null) {
                $responseParts['return'] = $this->wsdl->getType($method->getReturn()->getPhpType());
            }

            if (!empty($requestHeaderParts)) {
                $this->wsdl->addMessage($this->getRequestHeaderMessageName($method), $requestHeaderParts);
            }
            $this->wsdl->addMessage($this->getRequestMessageName($method), $requestParts);
            $this->wsdl->addMessage($this->getResponseMessageName($method), $responseParts);

            $portOperation = $this->wsdl->addPortOperation(
                $port,
                $method->getName(),
                $this->qualify($this->getRequestMessageName($method)),
                $this->qualify($this->getResponseMessageName($method))
            );

            $baseBinding   =
            $inputBinding  =
            $outputBinding = array(
                'use'           => 'literal',
                'namespace'     => $definition->getNamespace(),
                'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/',
            );

            if (!empty($requestParts)) {
                $portOperation->setAttribute('parameterOrder', implode(' ', array_keys($requestParts)));

                $inputBinding['parts'] = implode(' ', array_keys($requestParts));
            }

            if (!empty($responseParts)) {
                $outputBinding['parts'] = implode(' ', array_keys($responseParts));
            }

            $bindingOperation = $this->wsdl->addBindingOperation(
                $binding,
                $method->getName(),
                $inputBinding,
                $outputBinding
            );
            $bindingOperation = $this->wsdl->addBindingOperationHeader(
                $bindingOperation,
                array_keys($requestHeaderParts),
                array_merge(array('message' => $this->qualify($this->getRequestHeaderMessageName($method))), $baseBinding)
            );

            $this->wsdl->addSoapOperation($bindingOperation, $this->getSoapOperationName($method));
        }

        $this->definition = null;

        $dom               = $this->wsdl->toDomDocument();
        $dom->formatOutput = true;

        if ($this->options['stylesheet']) {
            $stylesheet = $dom->createProcessingInstruction('xml-stylesheet', sprintf('type="text/xsl" href="%s"', $this->options['stylesheet']));

            $dom->insertBefore($stylesheet, $dom->documentElement);
        }

        return $this->wsdl->toXml();
    }

    protected function qualify($name, $namespace = null)
    {
        if($namespace === null) {
            $namespace = $this->definition->getNamespace();
        }

        return $this->wsdl->toDomDocument()->lookupPrefix($namespace).':'.$name;
    }

    protected function getPortName()
    {
        return $this->definition->getName().'Port';
    }

    protected function getPortTypeName()
    {
        return $this->definition->getName().'PortType';
    }

    protected function getBindingName()
    {
        return $this->definition->getName().'Binding';
    }

    protected function getServiceName()
    {
        return $this->definition->getName().'Service';
    }

    protected function getRequestHeaderMessageName(Method $method)
    {
        return $method->getName().'Header';
    }

    protected function getRequestMessageName(Method $method)
    {
        return $method->getName().'Request';
    }

    protected function getResponseMessageName(Method $method)
    {
        return $method->getName().'Response';
    }

    protected function getSoapOperationName(Method $method)
    {
        return $this->definition->getNamespace().$method->getName();
    }
}
