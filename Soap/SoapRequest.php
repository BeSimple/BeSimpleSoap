<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Soap;

use Bundle\WebServiceBundle\Util\Collection;

use Symfony\Component\HttpFoundation\Request;

use Zend\Mime\Mime;
use Zend\Mime\Message;

/**
 * SoapRequest.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapRequest extends Request
{
    /**
     * @var string
     */
    protected $rawContent;

    /**
     * @var string
     */
    protected $soapMessage;

    /**
     * @var string
     */
    protected $soapAction;

    /**
     * @var \Bundle\WebServiceBundle\Util\Collection
     */
    protected $soapHeaders;

    /**
     * @var \Bundle\WebServiceBundle\Util\Collection
     */
    protected $soapAttachments;

    public function __construct($rawContent = null, array $query = null, array $attributes = null, array $cookies = null, array $server = null)
    {
        parent::__construct($query, null, $attributes, $cookies, null, $server);

        $this->rawContent = $rawContent != null ? $rawContent : $this->loadRawContent();
        $this->soapMessage = null;
        $this->soapHeaders = new Collection('getName');
        $this->soapAttachments = new Collection('getId');
    }

    /**
     * Gets raw data send to the server.
     *
     * @return string
     */
    public function getRawContent()
    {
        return $this->rawContent;
    }

    /**
     * Gets the XML string of the SOAP message.
     *
     * @return string
     */
    public function getSoapMessage()
    {
        if($this->soapMessage === null)
        {
            $this->soapMessage = $this->initializeSoapMessage();
        }

        return $this->soapMessage;
    }

    public function getSoapHeaders()
    {
        return $this->soapHeaders;
    }

    public function getSoapAttachments()
    {
        return $this->soapAttachments;
    }

    /**
     * Loads the plain HTTP POST data.
     *
     * @return string
     */
    protected function loadRawContent()
    {
        return isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');
    }

    protected function initializeSoapMessage()
    {
        if($this->server->has('CONTENT_TYPE'))
        {
            $type = $this->splitContentTypeHeader($this->server->get('CONTENT_TYPE'));

            switch($type['_type'])
            {
                case 'multipart/related':
                    if($type['type'] == 'application/xop+xml')
                    {
                        return $this->initializeMtomSoapMessage($type, $this->rawContent);
                    }
                    else
                    {
                        //log error
                    }
                    break;
                case 'application/soap+xml':
                    // goto fallback
                    break;
                default:
                    // log error
                    break;
            }
        }

        // fallback
        return $this->rawContent;
    }

    protected function initializeMtomSoapMessage(array $contentTypeHeader, $content)
    {
        if(!isset($contentTypeHeader['start']) || !isset($contentTypeHeader['boundary']))
        {
            throw new \InvalidArgumentException();
        }

        $mimeMessage = Message::createFromMessage($content, $contentTypeHeader['boundary']);
        $mimeParts = $mimeMessage->getParts();

        $soapMimePartId = trim($contentTypeHeader['start'], '<>');
        $soapMimePartType = $contentTypeHeader['start-info'];

        $rootPart = array_shift($mimeParts);
        $rootPartType = $this->splitContentTypeHeader($rootPart->type);

        // TODO: add more checks to achieve full compatibility to MTOM spec
        // http://www.w3.org/TR/soap12-mtom/
        if($rootPart->id != $soapMimePartId || $rootPartType['_type'] != 'application/xop+xml' || $rootPartType['type'] != $soapMimePartType)
        {
            throw new \InvalidArgumentException();
        }

        foreach($mimeParts as $mimePart)
        {
            $this->soapAttachments->add(new SoapAttachment(
                $mimePart->id,
                $mimePart->type,
                // handle content decoding / prevent encoding
                $mimePart->getContent()
            ));
        }

        // handle content decoding / prevent encoding
        return $rootPart->getContent();
    }

    protected function splitContentTypeHeader($header)
    {
        $result = array();
        $parts = explode(';', strtolower($header));

        $result['_type'] = array_shift($parts);

        foreach($parts as $part)
        {
            list($key, $value) = explode('=', trim($part), 2);

            $result[$key] = trim($value, '"');
        }

        return $result;
    }
}