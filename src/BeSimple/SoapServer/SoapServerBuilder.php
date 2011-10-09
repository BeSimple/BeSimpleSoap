<?php

/*
 * This file is part of the BeSimpleSoapServer.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapServer;

use BeSimple\SoapCommon\AbstractSoapBuilder;
use BeSimple\SoapCommon\Converter\TypeConverterInterface;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;

/**
 * SoapServerBuilder provides a fluent interface to configure and create a SoapServer instance.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapServerBuilder extends AbstractSoapBuilder
{
    protected $persistence;
    protected $errorReporting;

    protected $handlerClass;
    protected $handlerObject;

    /**
     * @return SoapServerBuilder
     */
    static public function createWithDefaults()
    {
        return parent::createWithDefaults()
            ->withErrorReporting(false)
        ;
    }

    /**
     * Initializes all options with the defaults used in the native SoapServer.
     */
    public function __construct()
    {
        parent::__construct();

        $this->persistence = SOAP_PERSISTENCE_REQUEST;

        // TODO: this is not the default, but safer
        $this->withErrorReporting(false);

        $this->options['classmap'] = array();
        $this->options['typemap']  = array();
    }

    public function build()
    {
        $this->validateOptions();

        use_soap_error_handler($this->errorReporting);

        $server = new SoapServer($this->wsdl, $this->options);
        $server->setPersistence($this->persistence);

        if (null !== $this->handlerClass) {
            $server->setClass($this->handlerClass);
        } elseif (null !== $this->handlerObject) {
            $server->setObject($this->handlerObject);
        }

        return $server;
    }

    public function withActor($actor)
    {
        $this->options['actor'] = $actor;

        return $this;
    }

    /**
     * Enables the HTTP session. The handler object is persisted between multiple requests in a session.
     */
    public function withHttpSession()
    {
        $this->persistence = SOAP_PERSISTENCE_SESSION;

        return $this;
    }

    /**
     * Enables reporting of internal errors to clients. This should only be enabled in development environments.
     *
     * @param boolean $enable
     */
    public function withErrorReporting($enable = true)
    {
        $this->errorReporting = $enable;

        return $this;
    }

    public function withBase64Attachments()
    {
        return $this;
    }

    public function withSwaAttachments()
    {
        return $this;
    }

    public function withMtomAttachments()
    {
        return $this;
    }

    /**
     * @param mixed $handler Can be either a class name or an object.
     *
     * @return SoapServerBuilder
     */
    public function withHandler($handler)
    {
        if (is_string($handler) && class_exists($handler)) {
            $this->handlerClass  = $handler;
            $this->handlerObject = null;
        } elseif (is_object($handler)) {
            $this->handlerClass  = null;
            $this->handlerObject = $handler;
        } else {
            throw new \InvalidArgumentException('The handler has to be a class name or an object');
        }

        return $this;
    }

    public function withTypeConverter(TypeConverterInterface $converter)
    {
        $this->withTypeMapping($converter->getTypeNamespace(), $converter->getTypeName(), array($converter, 'convertXmlToPhp'), array($converter, 'convertPhpToXml'));

        return $this;
    }

    public function withTypeConverters(TypeConverterCollection $converters, $merge = true)
    {
        $this->withTypemap($converters->getTypemap(), $merge);

        return $this;
    }

    /**
     * Adds a type mapping to the typemap.
     *
     * @param string $xmlNamespace
     * @param string $xmlType
     * @param callable $fromXmlCallback
     * @param callable $toXmlCallback
     */
    public function withTypeMapping($xmlNamespace, $xmlType, $fromXmlCallback, $toXmlCallback)
    {
        $this->options['typemap'][] = array(
            'type_ns'   => $xmlNamespace,
            'type_name' => $xmlType,
            'from_xml'  => $fromXmlCallback,
            'to_xml'    => $toXmlCallback
        );

        return $this;
    }

    /**
     * Sets the typemap.
     *
     * @param array $typemap The typemap.
     * @param boolean $merge If true the given typemap is merged into the existing one, otherwise the existing one is overwritten.
     */
    public function withTypemap($typemap, $merge = true)
    {
        if ($merge) {
            $this->options['typemap'] = array_merge($this->options['typemap'], $typemap);
        } else {
            $this->options['typemap'] = $typemap;
        }

        return $this;
    }

    /**
     * Adds a class mapping to the classmap.
     *
     * @param string $xmlType
     * @param string $phpType
     */
    public function withClassMapping($xmlType, $phpType)
    {
        $this->options['classmap'][$xmlType] = $phpType;

        return $this;
    }

    /**
     * Sets the classmap.
     *
     * @param array $classmap The classmap.
     * @param boolean $merge If true the given classmap is merged into the existing one, otherwise the existing one is overwritten.
     */
    public function withClassmap($classmap, $merge = true)
    {
        if ($merge) {
            $this->options['classmap'] = array_merge($this->options['classmap'], $classmap);
        } else {
            $this->options['classmap'] = $classmap;
        }

        return $this;
    }

    protected function validateOptions()
    {
        $this->validateWsdl();

        if (null === $this->handlerClass && null === $this->handlerObject) {
            throw new \InvalidArgumentException('The handler has to be configured!');
        }
    }
}