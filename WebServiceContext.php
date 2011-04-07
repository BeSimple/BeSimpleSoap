<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle;

/**
 * WebServiceContext.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
use Bundle\WebServiceBundle\ServiceDefinition\Dumper\DumperInterface;

use Bundle\WebServiceBundle\ServiceBinding\MessageBinderInterface;

use Bundle\WebServiceBundle\Converter\ConverterRepository;

use Bundle\WebServiceBundle\ServiceBinding\ServiceBinder;

use Bundle\WebServiceBundle\Soap\SoapServerFactory;

class WebServiceContext
{
    private $converterRepository;
    private $requestMessageBinder;
    private $responseMessageBinder;
    
    private $serviceDefinitionLoader;
    private $wsdlFileDumper;
    
    private $options;
    
    private $serviceDefinition;
    private $serviceBinder;
    private $serverFactory;
    
    public function __construct(LoaderInterface $loader, DumperInterface $dumper, ConverterRepository $converterRepository, MessageBinderInterface $requestMessageBinder, MessageBinderInterface $responseMessageBinder, array $options)
    {
        $this->serviceDefinitionLoader = $loader;
        $this->wsdlFileDumper = $dumper;
        
        $this->converterRepository = $converterRepository;
        $this->requestMessageBinder = $requestMessageBinder;
        $this->responseMessageBinder = $responseMessageBinder;
        
        $this->options = $options;
    }
    
    public function getServiceDefinition() 
    {
        if($this->serviceDefinition === null)
        {
            
        }
        
        ;
    }
    
    public function getWsdlFile() 
    {
        ;
    }
    
    public function getServiceBinder() 
    {
        if($this->serviceBinder === null)
        {
            $this->serviceBinder = new ServiceBinder($this->getServiceDefinition(), $this->requestMessageBinder, $this->responseMessageBinder);
        }
        
        return $this->serviceBinder;
    }
    
    public function getServerFactory() 
    {
        if($this->serverFactory === null)
        {
            $this->serverFactory = new SoapServerFactory($this->getServiceDefinition(), $this->getWsdlFile(), $this->converterRepository);
        }
        
        return $this->serverFactory;
    }
}