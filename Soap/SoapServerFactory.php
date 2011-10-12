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

use BeSimple\SoapCommon\Cache;
use BeSimple\SoapCommon\Classmap;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;

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

    public function __construct($wsdlFile, Classmap $classmap, TypeConverterCollection $converters, array $options = array())
    {
        $this->wsdlFile   = $wsdlFile;
        $this->classmap   = $classmap;
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
                'classmap'   => $this->classmap->all(),
                'typemap'    => $this->converters->getTypemap(),
                'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
                'cache_wsdl' => null !== $this->options['cache_type'] ? $this->options['cache_type'] : Cache::getType(),
            )
        );
    }
}
