<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Soap;

use BeSimple\SoapBundle\Converter\ConverterRepository;
use BeSimple\SoapCommon\Cache;
use Zend\Soap\Wsdl;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapServerFactory
{
    private $wsdlFile;
    private $classmap;
    private $converters;
    private $debug;

    public function __construct($wsdlFile, array $classmap, ConverterRepository $converters, $debug = false)
    {
        $this->wsdlFile   = $wsdlFile;
        $this->classmap   = $this->fixSoapServerClassmap($classmap);
        $this->converters = $converters;
        $this->debug      = $debug;
    }

    public function create($request, $response)
    {
        return new \SoapServer(
            $this->wsdlFile,
            array(
                'classmap'   => $this->classmap,
                'typemap'    => $this->createSoapServerTypemap($request, $response),
                'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
                'cache_wsdl' => Cache::getType(),
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

    private function fixSoapServerClassmap($classmap)
    {
        $classmapFixed = array();

        foreach ($classmap as $class => $definition) {
            $classmapFixed[Wsdl::translateType($class)] = $class;
        }

        return $classmapFixed;
    }
}