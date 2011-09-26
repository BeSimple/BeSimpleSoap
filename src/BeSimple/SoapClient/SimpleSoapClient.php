<?php

/*
 * This file is part of the BeSimpleSoapClient.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapClient;

use BeSimple\SoapCommon\Cache;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class SimpleSoapClient
{
    protected $wsdl;
    protected $converters;
    protected $soapClient;

    /**
     * @param string $wsdl
     * @param array  $options
     */
    public function __construct($wsdl, TypeConverterCollection $converters = null, array $options = array())
    {
        $this->wsdl       = $wsdl;
        $this->converters = $converters;
        $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        $this->options = array(
            'debug'      => false,
            'cache_type' => null,
            'namespace'  => null,
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
                __CLASS__,
                implode('\', \'', $invalid)
            ));
        }
    }

    /**
     * @param string $name  The name
     * @param mixed  $value The value
     *
     * @throws \InvalidArgumentException
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException(sprintf(
                'The "%s" class does not support the "%s" option.',
                __CLASS__,
                $name
            ));
        }

        $this->options[$name] = $value;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $key The key
     *
     * @return mixed The value
     *
     * @throws \InvalidArgumentException
     */
    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf(
                'The "%s" class does not support the "%s" option.',
                __CLASS__,
                $key
            ));
        }

        return $this->options[$key];
    }

    /**
     * @param SoapRequest $soapRequest
     *
     * @return mixed
     */
    public function send(SoapRequest $soapRequest)
    {
        return $this->getNativeSoapClient()->__soapCall(
            $soapRequest->getFunction(),
            $soapRequest->getArguments(),
            $soapRequest->getOptions(),
            $soapRequest->getHeaders()
        );
    }

    /**
     * @param string The SoapHeader name
     * @param mixed  The SoapHeader value
     *
     * @return \SoapHeader
     */
    public function createSoapHeader($name, $value)
    {
        if (null === $namespace = $this->getOption('namespace')) {
            throw new \RuntimeException('You cannot create SoapHeader if you do not specify a namespace.');
        }

        return new \SoapHeader($namespace, $name, $value);
    }

    /**
     * @return \SoapClient
     */
    public function getNativeSoapClient()
    {
        if (!$this->soapClient) {
            $this->soapClient = new \SoapClient($this->wsdl, $this->getSoapOptions());
        }

        return $this->soapClient;
    }

    /**
     * @return array The \SoapClient options
     */
    public function getSoapOptions()
    {
        $options = array();

        if (null === $this->options['cache_type']) {
            $this->options['cache_type'] = Cache::getType();
        }

        $options['cache_wsdl'] = $this->options['cache_type'];
        $options['trace']      = $this->options['debug'];
        $options['typemap']    = $this->getTypemap();

        return $options;
    }

    /**
     * @return array
     */
    protected function getTypemap()
    {
        if (!$this->converters) {
            return array();
        }

        return $this->converters->getTypemap();
    }
}