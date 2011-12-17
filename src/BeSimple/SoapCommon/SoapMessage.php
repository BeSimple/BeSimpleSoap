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
        SOAP_1_1 => 'text/xml; charset=utf-8',
        SOAP_1_2 => 'application/soap+xml; charset=utf-8'
    );

    static public function getContentTypeForVersion($version)
    {
        if(!in_array($soapVersion, array(SOAP_1_1, SOAP_1_2))) {
            throw new \InvalidArgumentException("The 'version' argument has to be either 'SOAP_1_1' or 'SOAP_1_2'!");
        }

        return self::$versionToContentTypeMap[$version];
    }

    protected $contentType;
    protected $content;

    protected $contentDomDocument = null;

    protected $version;
    protected $action;
    protected $location;

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
        if (null !== $this->contentDomDocument) {
            $this->content = $this->contentDomDocument->saveXML();
        }
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        if (null !== $this->contentDomDocument) {
            $this->contentDomDocument->loadXML($this->content);
        }
    }

    public function getContentDocument()
    {
        if (null === $this->contentDomDocument) {
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

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }
}