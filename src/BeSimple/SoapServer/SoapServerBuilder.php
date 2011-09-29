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

use BeSimple\SoapCommon\Cache;
use BeSimple\SoapCommon\Converter\TypeConverterInterface;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;

/**
 * SoapServerBuilder provides a fluent interface to configure and create a SoapServer instance.
 * 
 * @author Christian Kerl
 */
class SoapServerBuilder
{
    public static function createEmpty()
    {
        return new self();
    }
    
    public static function createWithDefaults()
    {
        $builder = new self();
        $builder
            ->withSoapVersion12()
            ->withEncoding('UTF-8')
            ->withNoWsdlCache()
            ->withErrorReporting(false)
            ->withSingleElementArrays()
        ;
        
        return $builder;
    }
    
    private $options;
    
    private $optionWsdl = null;
    private $optionPersistence;
    private $optionErrorReporting;
    
    private $optionHandlerClass = null;
    private $optionHandlerObject = null;
    
    /**
     * Initializes all options with the defaults used in the native SoapServer.
     */
    private function __construct()
    {
        $this->optionPersistence = SOAP_PERSISTENCE_REQUEST;
        
        // TODO: this is not the default, but safer
        $this->optionErrorReporting = false;
        
        $this->options = array(
        	'soap_version' => SOAP_1_1,
        	'cache_wsdl'   => Cache::getType(),
        	'classmap'     => array(),
        	'typemap'      => array(),
        	'features'     => 0
        );
    }
    
    public function build()
    {
        $this->validateOptions();
        
        use_soap_error_handler($this->optionErrorReporting);
        
        $server = new SoapServer($this->optionWsdl, $this->options);
        $server->setPersistence($this->optionPersistence);
        
        if(null !== $this->optionHandlerClass)
        {
            $server->setClass($this->optionHandlerClass);
        }
        
        if(null !== $this->optionHandlerObject)
        {
            $server->setObject($this->optionHandlerObject);
        }
        
        return $server;
    }
    
    private function validateOptions()
    {
        if(null === $this->optionWsdl)
        {
            throw new \InvalidArgumentException('The WSDL has to be configured!');
        }
        
        if(null === $this->optionHandlerClass && null === $this->optionHandlerObject)
        {
            throw new \InvalidArgumentException('The handler has to be configured!');
        }
    }
    
    public function withWsdl($wsdl)
    { 
        $this->optionWsdl = $wsdl;
        
        return $this; 
    }
    
    public function withSoapVersion11()
    {
        $this->options['soap_version'] = SOAP_1_1;
        
        return $this; 
    }
    
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
        $this->optionPersistence = SOAP_PERSISTENCE_SESSION;
        
        return $this;
    }
    
    /**
     * Enables reporting of internal errors to clients. This should only be enabled in development environments.
     * 
     * @param boolean $enable
     */
    public function withErrorReporting($enable = true)
    {
        $this->optionErrorReporting = $enable;
        
        return $this;
    }
    
    public function withNoWsdlCache()
    { 
        $this->options['cache_wsdl'] = Cache::TYPE_NONE;
        
        return $this; 
    }
    
    public function withWsdlDiskCache()
    { 
        $this->options['cache_wsdl'] = Cache::TYPE_DISK;
        
        return $this; 
    }
    
    public function withWsdlMemoryCache()
    { 
        $this->options['cache_wsdl'] = Cache::TYPE_MEMORY;
        
        return $this;
    }
    
    public function withWsdlDiskAndMemoryCache()
    { 
        $this->options['cache_wsdl'] = Cache::TYPE_DISK_MEMORY;
        
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
     * Enables the SOAP_SINGLE_ELEMENT_ARRAYS feature. 
     * 
     * If enabled arrays containing only one element will be passed as arrays otherwise the single element is extracted and directly passed.
     */
    public function withSingleElementArrays()
    {
        $this->options['features'] |= SOAP_SINGLE_ELEMENT_ARRAYS;
        
        return $this; 
    }
    
    /**
     * Enables the SOAP_WAIT_ONE_WAY_CALLS feature. 
     */
    public function withWaitOneWayCalls()
    {
        $this->options['features'] |= SOAP_WAIT_ONE_WAY_CALLS;
        
        return $this; 
    }
    
    /**
     * Enables the SOAP_USE_XSI_ARRAY_TYPE feature. 
     */
    public function withUseXsiArrayType()
    {
        $this->options['features'] |= SOAP_USE_XSI_ARRAY_TYPE;
        
        return $this; 
    }
    
    /**
     * 
     * 
     * @param mixed $handler Can be either a class name or an object.
     */
    public function withHandler($handler)
    {
        if(is_string($handler) && class_exists($handler))
        {
            $this->optionHandlerClass = $handler;
            $this->optionHandlerObject = null;
        }
        
        if(is_object($handler))
        {
            $this->optionHandlerClass = null;
            $this->optionHandlerObject = $handler;
        }
        
        throw new \InvalidArgumentException('The handler has to be a class name or an object!');
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
            'type_ns'	=> $xmlNamespace,
            'type_name' => $xmlType,
            'from_xml'  => $fromXmlCallback,
            'to_xml'	=> $toXmlCallback
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
        if($merge)
        {
            $this->options['typemap'] = array_merge($this->options['typemap'], $typemap);
        }
        else
        {
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
        if($merge)
        {
            $this->options['classmap'] = array_merge($this->options['classmap'], $classmap);
        }
        else
        {
            $this->options['classmap'] = $classmap;
        }
        
        return $this;
    }
}
