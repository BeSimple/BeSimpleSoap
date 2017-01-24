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

use BeSimple\SoapCommon\Definition\Definition;
use BeSimple\SoapCommon\Definition\Method;
use BeSimple\SoapCommon\Definition\Type\ArrayOfType;
use BeSimple\SoapCommon\Definition\Type\ComplexType;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Dumper
{
    const XML_NS = 'xmlns';
    const XML_NS_URI = 'http://www.w3.org/2000/xmlns/';

    const WSDL_NS = 'wsdl';
    const WSDL_NS_URI = 'http://schemas.xmlsoap.org/wsdl/';

    const SOAP_NS = 'soap';
    const SOAP_NS_URI = 'http://schemas.xmlsoap.org/wsdl/soap/';

    const SOAP12_NS = 'soap12';
    const SOAP12_NS_URI = 'http://schemas.xmlsoap.org/wsdl/soap12/';

    const SOAP_ENC_NS = 'soap-enc';
    const SOAP_ENC_URI = 'http://schemas.xmlsoap.org/soap/encoding/';

    const XSD_NS = 'xsd';
    const XSD_NS_URI = 'http://www.w3.org/2001/XMLSchema';

    const TYPES_NS = 'tns';

    protected $definition;
    protected $options;

    protected $version11;
    protected $version12;

    protected $document;
    protected $domDefinitions;
    protected $domSchema;
    protected $domService;
    protected $domPortType;

    public function __construct(Definition $definition, array $options = array())
    {
        $this->definition = $definition;
        $this->document = new \DOMDocument('1.0', 'utf-8');

        $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        $this->options = array(
            'version11_class' => 'BeSimple\\SoapWsdl\\Dumper\\Version11',
            'version12_class' => 'BeSimple\\SoapWsdl\\Dumper\\Version12',
            'version11_name' => $this->definition->getName(),
            'version12_name' => $this->definition->getName().'12',
            'stylesheet' => null,
        );

        $invalid = array();
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new \InvalidArgumentException(sprintf('The Definition does not support the following options: "%s"', implode('", "', $invalid)));
        }

        return $this;
    }

    public function setOption($key, $value)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Definition does not support the "%s" option.', $key));
        }

        $this->options[$key] = $value;

        return $this;
    }

    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Definition does not support the "%s" option.', $key));
        }

        return $this->options[$key];
    }

    public function dump()
    {
        $this->addDefinitions();
        $this->addMethods();
        $this->addService();

        foreach (array($this->version11, $this->version12) as $version) {
            if (!$version) {
                continue;
            }

            $this->appendVersion($version);
        }

        $this->document->formatOutput = true;

        $this->addStylesheet();

        return $this->document->saveXML();
    }

    protected function appendVersion(VersionInterface $version)
    {
        $binding = $version->getBindingNode();
        $binding = $this->document->importNode($binding, true);
        $this->domDefinitions->appendChild($binding);

        $servicePort = $version->getServicePortNode();
        $servicePort = $this->document->importNode($servicePort, true);
        $this->domService->appendChild($servicePort);
    }

    protected function addService()
    {
        $this->domService = $this->document->createElement('service');
        $this->domService->setAttribute('name', $this->definition->getName().'Service');

        $this->domDefinitions->appendChild($this->domService);

        return $this->domService;
    }

    protected function addDefinitions()
    {
        $this->domDefinitions = $this->document->createElement('definitions');
        $this->domDefinitions->setAttributeNS(static::XML_NS_URI, static::XML_NS, static::WSDL_NS_URI);
        $this->domDefinitions->setAttributeNS(static::XML_NS_URI, static::XML_NS.':'.static::TYPES_NS, $this->definition->getNamespace());
        $this->domDefinitions->setAttributeNS(static::XML_NS_URI, static::XML_NS.':'.static::SOAP_NS, static::SOAP_NS_URI);
        $this->domDefinitions->setAttributeNS(static::XML_NS_URI, static::XML_NS.':'.static::SOAP12_NS, static::SOAP12_NS_URI);
        $this->domDefinitions->setAttributeNS(static::XML_NS_URI, static::XML_NS.':'.static::XSD_NS, static::XSD_NS_URI);
        $this->domDefinitions->setAttributeNS(static::XML_NS_URI, static::XML_NS.':'.static::SOAP_ENC_NS, static::SOAP_ENC_URI);
        $this->domDefinitions->setAttributeNS(static::XML_NS_URI, static::XML_NS.':'.static::WSDL_NS, static::WSDL_NS_URI);

        foreach ($this->definition->getTypeRepository()->getXmlNamespaces() as $prefix => $uri) {
            $this->domDefinitions->setAttributeNs(static::XML_NS_URI, static::XML_NS.':'.$prefix, $uri);
        }

        $this->domDefinitions->setAttribute('name', $this->definition->getName());
        $this->domDefinitions->setAttribute('targetNamespace', $this->definition->getNamespace());

        $this->document->appendChild($this->domDefinitions);
    }

    protected function addMethods()
    {
        $this->addPortType();
        $this->addComplexTypes();
        $this->addMessages($this->definition->getMessages());

        foreach ($this->definition->getMethods() as $method) {
            $this->addPortOperation($method);

            foreach ($method->getVersions() as $version) {
                $this->getVersion($version)->addOperation($method);
            }
        }
    }

    protected function addMessages(array $messages)
    {
        foreach ($messages as $message) {
            if (preg_match('#Header$#', $message->getName()) && $message->isEmpty()) {
                continue;
            }

            $messageElement = $this->document->createElement('message');
            $messageElement->setAttribute('name', $message->getName());

            foreach ($message->all() as $part) {
                $type = $this->definition->getTypeRepository()->getType($part->getType());

                $partElement = $this->document->createElement('part');
                $partElement->setAttribute('name', $part->getName());

                if ($type instanceof ComplexType) {
                    $partElement->setAttribute('type', static::TYPES_NS.':'.$type->getXmlType());
                } else {
                    $partElement->setAttribute('type', $type);
                }

                $messageElement->appendChild($partElement);
            }

            $this->domDefinitions->appendChild($messageElement);
        }
    }

    protected function addComplexTypes()
    {
        $types = $this->document->createElement('types');
        $this->domDefinitions->appendChild($types);

        $this->domSchema = $this->document->createElement(static::XSD_NS.':schema');
        $this->domSchema->setAttribute('targetNamespace', $this->definition->getNamespace());
        $types->appendChild($this->domSchema);

        foreach ($this->definition->getTypeRepository()->getComplexTypes() as $type) {
            $this->addComplexType($type);
        }

        return $types;
    }

    protected function addComplexType(ComplexType $type)
    {
        $complexType = $this->document->createElement(static::XSD_NS.':complexType');
        $complexType->setAttribute('name', $type->getXmlType());

        $all = $this->document->createElement(static::XSD_NS.':'.($type instanceof ArrayOfType ? 'sequence' : 'all'));
        $complexType->appendChild($all);

        foreach ($type->all() as $child) {
            $childType = $this->definition->getTypeRepository()->getType($child->getType());

            $element = $this->document->createElement(static::XSD_NS.':element');
            $element->setAttribute('name', $child->getName());

            if ($childType instanceof ComplexType) {
                $name = $childType->getXmlType();
                if ($childType instanceof ArrayOfType) {
                    $name = $childType->getName();
                }

                $element->setAttribute('type', static::TYPES_NS.':'.$name);
            } else {
                $element->setAttribute('type', $childType);
            }

            if ($child->isNillable()) {
                $element->setAttribute('nillable', 'true');
            }

            if ($type instanceof ArrayOfType) {
                $element->setAttribute('minOccurs', 0);
                $element->setAttribute('maxOccurs', 'unbounded');
            }

            $all->appendChild($element);
        }

        $this->domSchema->appendChild($complexType);
    }

    protected function addPortType()
    {
        $this->domPortType = $this->document->createElement('portType');
        $this->domPortType->setAttribute('name', $this->definition->getName().'PortType');

        $this->domDefinitions->appendChild($this->domPortType);
    }

    protected function addPortOperation(Method $method)
    {
        $operation = $this->document->createElement('operation');
        $operation->setAttribute('name', $method->getName());

        foreach (array('input' => $method->getInput(), 'output' => $method->getOutput(), 'fault' => $method->getFault()) as $type => $message) {
            if ('fault' === $type && $message->isEmpty()) {
                continue;
            }

            $node = $this->document->createElement($type);
            $node->setAttribute('message', static::TYPES_NS.':'.$message->getName());

            $operation->appendChild($node);
        }

        $this->domPortType->appendChild($operation);

        return $operation;
    }

    protected function addStylesheet()
    {
        if ($this->options['stylesheet']) {
            $stylesheet = $this->document->createProcessingInstruction('xml-stylesheet', sprintf('type="text/xsl" href="%s"', $this->options['stylesheet']));

            $this->document->insertBefore($stylesheet, $this->document->documentElement);
        }
    }

    protected function getVersion($version)
    {
        if (\SOAP_1_2 === $version) {
            return $this->getVersion12();
        }

        return $this->getVersion11();
    }

    protected function getVersion11()
    {
        if (!$this->version11) {
            $this->version11 = new $this->options['version11_class'](
                static::SOAP_NS,
                static::TYPES_NS,
                $this->options['version11_name'],
                $this->definition->getNamespace(),
                static::TYPES_NS.':'.$this->definition->getName().'PortType',
                $this->definition->getOption('location'),
                $this->definition->getOption('style')
            );
        }

        return $this->version11;
    }

    protected function getVersion12()
    {
        if (!$this->version12) {
            $this->version12 = new $this->options['version12_class'](
                static::SOAP12_NS,
                static::TYPES_NS,
                $this->options['version12_name'],
                $this->definition->getNamespace(),
                static::TYPES_NS.':'.$this->definition->getName().'PortType',
                $this->definition->getOption('location'),
                $this->definition->getOption('style')
            );
        }

        return $this->version12;
    }
}
