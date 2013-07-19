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
use BeSimple\SoapCommon\Helper;

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
     * Create new instance with default options.
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
     */
    static public function createWithDefaults()
    {
        return parent::createWithDefaults()
            ->withErrorReporting(false);
    }

    /**
     * Initializes all options with the defaults used in the native SoapServer.
     */
    public function __construct()
    {
        parent::__construct();

        // TODO: this is not the default, but safer
        $this->withErrorReporting(false);
    }

    /**
     * Finally returns a SoapClient instance.
     *
     * @return \BeSimple\SoapServer\SoapServer
     */
    public function build()
    {
        $this->validateOptions();

        use_soap_error_handler($this->errorReporting);

        $server = new SoapServer($this->wsdl, $this->getSoapOptions());

        if (null !== $this->persistence) {
            $server->setPersistence($this->persistence);
        }

        if (null !== $this->handlerClass) {
            $server->setClass($this->handlerClass);
        } elseif (null !== $this->handlerObject) {
            $server->setObject($this->handlerObject);
        }

        return $server;
    }

    /**
     * Cofigures the SOAP actor.
     *
     * @param string $actor Actor name
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
     */
    public function withActor($actor)
    {
        $this->options['actor'] = $actor;

        return $this;
    }

    /**
     * Enables persistence.
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
     */
    public function withPersistanceRequest()
    {
        $this->persistence = SOAP_PERSISTENCE_REQUEST;

        return $this;
    }

    /**
     * Enables the HTTP session. The handler object is persisted between multiple requests in a session.
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
     */
    public function withPersistenceSession()
    {
        $this->persistence = SOAP_PERSISTENCE_SESSION;

        return $this;
    }

    /**
     * Enables reporting of internal errors to clients. This should only be enabled in development environments.
     *
     * @param boolean $enable Enable error reporting
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
     */
    public function withErrorReporting($enable = true)
    {
        $this->errorReporting = $enable;

        return $this;
    }

    /**
     * SOAP attachment type Base64.
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
     */
    public function withBase64Attachments()
    {
        $this->options['attachment_type'] = Helper::ATTACHMENTS_TYPE_BASE64;

        return $this;
    }

    /**
     * SOAP attachment type SwA.
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
     */
    public function withSwaAttachments()
    {
        $this->options['attachment_type'] = Helper::ATTACHMENTS_TYPE_SWA;

        return $this;
    }

    /**
     * SOAP attachment type MTOM.
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
     */
    public function withMtomAttachments()
    {
        $this->options['attachment_type'] = Helper::ATTACHMENTS_TYPE_MTOM;

        return $this;
    }

    /**
     * Configures the handler class or object.
     *
     * @param mixed $handler Can be either a class name or an object.
     *
     * @return \BeSimple\SoapServer\SoapServerBuilder
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

    /**
     * Validate options.
     */
    protected function validateOptions()
    {
        $this->validateWsdl();

        if (null === $this->handlerClass && null === $this->handlerObject) {
            throw new \InvalidArgumentException('The handler has to be configured!');
        }
    }
}