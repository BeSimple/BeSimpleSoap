<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 * (c) Andreas Schamberger <mail@andreass.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
abstract class SoapMessage
{
    const CONTENT_TYPE_HEADER = 'CONTENT_TYPE';
    const ACTION_HEADER = 'HTTP_SOAPACTION';
    
    static protected $versionToContentTypeMap = array(
        SOAP_1_1 => 'text/xml; charset=%s',
        SOAP_1_2 => 'application/soap+xml; charset=%s'
    );
    
    static public function getContentTypeForVersion($version, $encoding = 'utf-8')
    {
        if(!in_array($soapVersion, array(SOAP_1_1, SOAP_1_2)))
        {
            throw new \InvalidArgumentException("The 'version' argument has to be either 'SOAP_1_1' or 'SOAP_1_2'!");
        }
        
        return sprintf(self::$versionToContentTypeMap[$version], $encoding);
    }
    
    protected $contentType;
    protected $content;
    
    protected $contentDomDocument = null;
    
    protected $version;
    protected $action;

    public function getContentType()
    {
        return $this->contentType;
    }
    
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    public function getContentDocument()
    {
        if(null === $this->contentDomDocument)
        {
            $this->contentDomDocument = new \DOMDocument();
            $this->contentDomDocument->loadXML($this->content);
        }
        
        return $this->contentDomDocument;
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    public function setVersion($version)
    {
        $this->version = $version;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function setAction($action)
    {
        $this->action = $action;
    }
}