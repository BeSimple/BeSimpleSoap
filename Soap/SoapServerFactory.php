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
        $this->classmap   = $classmap;
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
}