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
    private $options;

    public function __construct($wsdlFile, array $classmap, ConverterRepository $converters, array $options = array())
    {
        $this->wsdlFile   = $wsdlFile;
        $this->classmap   = $this->fixSoapServerClassmap($classmap);
        $this->converters = $converters;
        $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        $this->options = array(
            'debug'      => false,
            'cache_type' => null,
        );

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid   = array();
        $isInvalid = false;
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $isInvalid = true;
                $invalid[] = $key;
            }
        }

        if ($isInvalid) {
            throw new \InvalidArgumentException(sprintf(
                'The "%s" class does not support the following options: "%s".',
                get_class($this),
                implode('\', \'', $invalid)
            ));
        }
    }

    public function create($request, $response)
    {
        return new \SoapServer(
            $this->wsdlFile,
            array(
                'classmap'   => $this->classmap,
                'typemap'    => $this->createSoapServerTypemap($request, $response),
                'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
                'cache_wsdl' => null !== $this->options['cache_type'] ? $this->options['cache_type'] : Cache::getType(),
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