<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Soap;

use Bundle\WebServiceBundle\Converter\ConverterRepository;
use Bundle\WebServiceBundle\ServiceDefinition\ServiceDefinition;
use Bundle\WebServiceBundle\ServiceDefinition\Type;
use Bundle\WebServiceBundle\Util\QName;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapServerFactory
{
    private $definition;
    private $converters;
    private $wsdlFile;
    private $debug;

    public function __construct(ServiceDefinition $definition, $wsdlFile, ConverterRepository $converters, $debug = false)
    {
        $this->definition = $definition;
        $this->wsdlFile   = $wsdlFile;
        $this->converters = $converters;
        $this->debug      = $debug;
    }

    public function create($request, $response)
    {
        return new \SoapServer(
            $this->wsdlFile,
            array(
                'classmap'   => $this->createSoapServerClassmap(),
                'typemap'    => $this->createSoapServerTypemap($request, $response),
                'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
                'cache_wsdl' => $this->debug ? WSDL_CACHE_NONE : WSDL_CACHE_DISK,
            )
        );
    }

    private function createSoapServerTypemap($request, $response)
    {
        $typemap = array();

        foreach($this->converters->getTypeConverters() as $typeConverter) {
            $typemap[] = array(
                'type_name' => $typeConverter->getTypeName(),
                'type_ns'   => $typeConverter->getTypeNamespace(),
                'from_xml'  => function($input) use ($request, $typeConverter) {
                    return $typeConverter->convertXmlToPhp($request, $input);
                },
                'to_xml'    => function($input) use ($response, $typeConverter) {
                    return $typeConverter->convertPhpToXml($response, $input);
                },
            );
        }

        return $typemap;
    }

    private function createSoapServerClassmap()
    {
        $classmap = array();

        foreach($this->definition->getHeaders() as $header) {
            $this->addSoapServerClassmapEntry($classmap, $header->getType());
        }

        foreach($this->definition->getMethods() as $method) {
            foreach($method->getArguments() as $arg) {
                $this->addSoapServerClassmapEntry($classmap, $arg->getType());
            }
        }

        return $classmap;
    }

    private function addSoapServerClassmapEntry(&$classmap, Type $type)
    {
        // TODO: fix this hack
        if(null === $type->getXmlType()) return;

        $xmlType = QName::fromPackedQName($type->getXmlType())->getName();
        $phpType = $type->getPhpType();

        if(isset($classmap[$xmlType]) && $classmap[$xmlType] != $phpType) {
            // log warning
        }

        $classmap[$xmlType] = $phpType;
    }
}