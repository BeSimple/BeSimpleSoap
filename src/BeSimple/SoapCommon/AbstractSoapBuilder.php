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

use BeSimple\SoapCommon\Cache;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
abstract class AbstractSoapBuilder
{
    protected $wsdl;
    protected $options;

    /**
     * @return AbstractSoapBuilder
     */
    static public function createWithDefaults()
    {
        $builder = new static();

        $builder
            ->withSoapVersion12()
            ->withEncoding('UTF-8')
            ->withSingleElementArrays()
        ;

        return $builder;
    }

    public function __construct()
    {
        $this->options = array(
            'features' => 0,
        );
    }

    public function getWsdl()
    {
        return $this->wsdl;
    }

    public function getOptions()
    {
        return $this->options;
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
        $this->options['soap_version'] = SOAP_1_1;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withSoapVersion12()
    {
        $this->options['soap_version'] = SOAP_1_2;

        return $this;
    }

    public function withEncoding($encoding)
    {
        $this->options['encoding'] = $encoding;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdlCacheNone()
    {
        $this->options['cache_wsdl'] = Cache::TYPE_NONE;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdlCacheDisk()
    {
        $this->options['cache_wsdl'] = Cache::TYPE_DISK;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdlCacheMemory()
    {
        $this->options['cache_wsdl'] = Cache::TYPE_MEMORY;

        return $this;
    }

    /**
     * @return AbstractSoapBuilder
     */
    public function withWsdlCacheDiskAndMemory()
    {
        $this->options['cache_wsdl'] = Cache::TYPE_DISK_MEMORY;

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
        $this->options['features'] |= SOAP_SINGLE_ELEMENT_ARRAYS;

        return $this;
    }

    /**
     * Enables the SOAP_WAIT_ONE_WAY_CALLS feature.
     *
     * @return AbstractSoapBuilder
     */
    public function withWaitOneWayCalls()
    {
        $this->options['features'] |= SOAP_WAIT_ONE_WAY_CALLS;

        return $this;
    }

    /**
     * Enables the SOAP_USE_XSI_ARRAY_TYPE feature.
     *
     * @return AbstractSoapBuilder
     */
    public function withUseXsiArrayType()
    {
        $this->options['features'] |= SOAP_USE_XSI_ARRAY_TYPE;

        return $this;
    }

    protected function validateWsdl()
    {
        if (null === $this->wsdl) {
            throw new \InvalidArgumentException('The WSDL has to be configured!');
        }
    }
}