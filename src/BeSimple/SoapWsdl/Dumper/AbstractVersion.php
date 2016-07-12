<?php

/*
 * This file is part of the BeSimpleSoap.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapWsdl\Dumper;

use BeSimple\SoapCommon\Definition\Method;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
abstract class AbstractVersion implements VersionInterface
{
    protected $soapNs;

    protected $typeNs;

    protected $name;

    protected $namespace;

    protected $portTypeName;

    protected $location;

    protected $style;

    protected $transport;

    protected $document;

    protected $bindingNode;

    protected $servicePortNode;

    protected $portType;

    public function __construct(
        $soapNs,
        $typeNs,
        $name,
        $namespace,
        $portTypeName,
        $location,
        $style = \SOAP_RPC,
        $transport = 'http://schemas.xmlsoap.org/soap/http',
        $portType = 'Port'
    ) {
        $this->soapNs = $soapNs;
        $this->typeNs = $typeNs;

        $this->name = $name;
        $this->namespace = $namespace;
        $this->portTypeName = $portTypeName;

        $this->location = $location;
        $this->style = $style;
        $this->transport = $transport;

        $this->portType = $portType;

        $this->document = new \DOMDocument('1.0', 'utf-8');
    }

    public function getBindingNode()
    {
        if (!$this->bindingNode) {
            $this->bindingNode = $this->document->createElement(Dumper::WSDL_NS . ':binding');
            $this->bindingNode->setAttribute('name', $this->name . 'Binding');
            $this->bindingNode->setAttribute('type', $this->portTypeName);

            $this->addSoapBinding();
        }

        return $this->bindingNode;
    }

    public function getServicePortNode()
    {
        if (!$this->servicePortNode) {
            $this->servicePortNode = $this->document->createElement('wsdl:port');
            $portType = ($this->portType) ?: 'Port';
            $this->servicePortNode->setAttribute('name', $this->name . $portType);
            $this->servicePortNode->setAttribute('binding', $this->typeNs . ':' . $this->name . 'Binding');

            $this->addSoapAddress();
        }

        return $this->servicePortNode;
    }

    public function addOperation(Method $method)
    {
        $operation = $this->document->createElement(Dumper::WSDL_NS . ':operation');
        $operation->setAttribute('name', $method->getName());

        $soapOperation = $this->document->createElement($this->soapNs . ':operation');
        $soapAction = $method->getOption('soapAction')
            ? $method->getOption('soapAction')
            : $this->namespace . '/#' . $method->getName();
        $soapActionRequired = $method->getOption('soapActionRequired')
            ? $method->getOption('soapActionRequired')
            : true;

        $soapOperation->setAttribute('soapAction', $soapAction);
        $soapOperation->setAttribute('soapActionRequired', $soapActionRequired ? 'true' : 'false');
        $operation->appendChild($soapOperation);

        $this->getBindingNode()->appendChild($operation);

        $use = \SOAP_LITERAL === $method->getUse() ? 'literal' : 'encoded';

        $input = $this->document->createElement(Dumper::WSDL_NS . ':input');
        $operation->appendChild($input);

        $soapBody = $this->document->createElement($this->soapNs . ':body');
        $soapBody->setAttribute('use', $use);
        $soapBody->setAttribute('namespace', $this->namespace);
        $soapBody->setAttribute('encodingStyle', $this->getEncodingStyle());
        $input->appendChild($soapBody);

        $headers = $method->getHeaders();
        if (!$headers->isEmpty()) {
            foreach ($headers->all() as $part) {
                $soapHeader = $this->document->createElement($this->soapNs . ':header');
                $soapHeader->setAttribute('message', $this->typeNs . ':' . $headers->getName());
                $soapHeader->setAttribute('part', \SOAP_RPC === $this->style ? $part->getName() : 'parameters');
                $soapHeader->setAttribute('use', $use);
                $soapHeader->setAttribute('namespace', $this->namespace);
                $soapHeader->setAttribute('encodingStyle', $this->getEncodingStyle());
                $input->appendChild($soapHeader);
            }
        }

        $output = $this->document->createElement(Dumper::WSDL_NS . ':output');
        $soapBody = $this->document->createElement($this->soapNs . ':body');
        $soapBody->setAttribute('use', $use);
        $soapBody->setAttribute('namespace', $this->namespace);
        $soapBody->setAttribute('encodingStyle', $this->getEncodingStyle());
        $output->appendChild($soapBody);
        $operation->appendChild($output);
    }

    protected function addSoapBinding()
    {
        $soapBinding = $this->document->createElement($this->soapNs . ':binding');
        $soapBinding->setAttribute('transport', $this->transport);
        $soapBinding->setAttribute('style', \SOAP_RPC === $this->style ? 'rpc' : 'document');

        $this->bindingNode->appendChild($soapBinding);

        return $soapBinding;
    }

    protected function addSoapAddress()
    {
        $soapAddress = $this->document->createElement($this->soapNs . ':address');
        $soapAddress->setAttribute('location', $this->location);

        $this->servicePortNode->appendChild($soapAddress);

        return $soapAddress;
    }
}
