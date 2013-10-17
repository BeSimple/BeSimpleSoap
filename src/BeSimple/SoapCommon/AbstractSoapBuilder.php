<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon;

use BeSimple\SoapCommon\Converter\TypeConverterCollection;
use BeSimple\SoapCommon\Converter\TypeConverterInterface;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
abstract class AbstractSoapBuilder
{
    protected $wsdl;
    protected $soapOptions = array();

    /**
     * @return AbstractSoapBuilder
     */
    static public function createWithDefaults()
    {
        $builder = new static();

        return $builder
            ->withSoapVersion12()
            ->withEncoding('UTF-8')
            ->withSingleElementArrays()
        ;
    }

    public function __construct()
    {
        $this->soapOptions['features'] = 0;
        $this->soapOptions['classmap'] = new Classmap();
        $this->soapOptions['typemap']  = new TypeConverterCollection();
    }

    public function getWsdl()
    {
        return $this->wsdl;
    }

    public function getSoapOptions()
    {
        $options = $this->soapOptions;

        $options['classmap'] = $this->soapOptions['classmap']->all();
        $options['typemap']  = $this->soapOptions['typemap']->getTypemap();

        return $options;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdl($wsdl)
    {
        $this->wsdl = $wsdl;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withSoapVersion11()
    {
        $this->soapOptions['soap_version'] = \SOAP_1_1;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withSoapVersion12()
    {
        $this->soapOptions['soap_version'] = \SOAP_1_2;

        return $this;
    }

    public function withEncoding($encoding)
    {
        $this->soapOptions['encoding'] = $encoding;

        return $this;
    }

    public function withWsdlCache($cache)
    {
        if (!in_array($cache, Cache::getTypes(), true)) {
            throw new \InvalidArgumentException();
        }

        $this->soapOptions['cache_wsdl'] = $cache;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdlCacheNone()
    {
        $this->soapOptions['cache_wsdl'] = Cache::TYPE_NONE;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdlCacheDisk()
    {
        $this->soapOptions['cache_wsdl'] = Cache::TYPE_DISK;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdlCacheMemory()
    {
        $this->soapOptions['cache_wsdl'] = Cache::TYPE_MEMORY;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdlCacheDiskAndMemory()
    {
        $this->soapOptions['cache_wsdl'] = Cache::TYPE_DISK_MEMORY;

        return $this;
    }

    /**
     * Enables the SOAP_SINGLE_ELEMENT_ARRAYS feature.
     * If enabled arrays containing only one element will be passed as arrays otherwise the single element is extracted and directly passed.
     *
     * @return AbstractSoapBuilder
     */
    public function withSingleElementArrays()
    {
        $this->soapOptions['features'] |= \SOAP_SINGLE_ELEMENT_ARRAYS;

        return $this;
    }

    /**
     * Enables the SOAP_WAIT_ONE_WAY_CALLS feature.
     *
     * @return AbstractSoapBuilder
     */
    public function withWaitOneWayCalls()
    {
        $this->soapOptions['features'] |= \SOAP_WAIT_ONE_WAY_CALLS;

        return $this;
    }

    /**
     * Enables the SOAP_USE_XSI_ARRAY_TYPE feature.
     *
     * @return AbstractSoapBuilder
     */
    public function withUseXsiArrayType()
    {
        $this->soapOptions['features'] |= \SOAP_USE_XSI_ARRAY_TYPE;

        return $this;
    }

    public function withTypeConverter(TypeConverterInterface $converter)
    {
        $this->soapOptions['typemap']->add($converter);

        return $this;
    }

    public function withTypeConverters(TypeConverterCollection $converters, $merge = true)
    {
        if ($merge) {
            $this->soapOptions['typemap']->addCollection($converters);
        } else {
            $this->soapOptions['typemap']->set($converters->all());
        }

        return $this;
    }

    /**
     * Adds a class mapping to the classmap.
     *
     * @param string $xmlType
     * @param string $phpType
     *
     * @return AbstractSoapBuilder
     */
    public function withClassMapping($xmlType, $phpType)
    {
        $this->soapOptions['classmap']->add($xmlType, $phpType);

        return $this;
    }

    /**
     * Sets the classmap.
     *
     * @param array   $classmap The classmap.
     * @param boolean $merge    If true the given classmap is merged into the existing one, otherwise the existing one is overwritten.
     *
     * @return AbstractSoapBuilder
     */
    public function withClassmap(Classmap $classmap, $merge = true)
    {
        if ($merge) {
            $this->soapOptions['classmap']->addClassmap($classmap);
        } else {
            $this->soapOptions['classmap']->set($classmap->all());
        }

        return $this;
    }

    protected function validateWsdl()
    {
        if (null === $this->wsdl) {
            throw new \InvalidArgumentException('The WSDL has to be configured!');
        }
    }
}
