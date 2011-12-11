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

/**
 * Soap request/response filter helper for manipulating SOAP messages.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class FilterHelper
{
    /**
     * DOMDocument on which the helper functions operate.
     *
     * @var \DOMDocument
     */
    protected $domDocument = null;

    /**
     * Namespaces added.
     *
     * @var array(string=>string)
     */
    protected $namespaces = array();

    /**
     * Constructor.
     *
     * @param \DOMDocument $domDocument
     */
    public function __construct(\DOMDocument $domDocument)
    {
        $this->domDocument = $domDocument;
    }

    /**
     * Add new soap header.
     *
     * @param \DOMElement $node
     * @param boolean     $mustUnderstand
     * @param string      $actor
     * @param string      $soapVersion
     * @return void
     */
    public function addHeaderElement(\DOMElement $node, $mustUnderstand = null, $actor = null, $soapVersion = SOAP_1_1)
    {
        $root = $this->domDocument->documentElement;
        $namespace = $root->namespaceURI;
        $prefix = $root->prefix;
        if (null !== $mustUnderstand) {
            $node->appendChild(new \DOMAttr($prefix . ':mustUnderstand', (int)$mustUnderstand));
        }
        if (null !== $actor) {
            $attributeName = ($soapVersion == SOAP_1_1) ? 'actor' : 'role';
            $node->appendChild(new \DOMAttr($prefix . ':' . $attributeName, $actor));
        }
        $nodeListHeader = $root->getElementsByTagNameNS($namespace, 'Header');
        // add header if not there
        if ($nodeListHeader->length == 0) {
            // new header element
            $header = $this->domDocument->createElementNS($namespace, $prefix . ':Header');
            // try to add it before body
            $nodeListBody = $root->getElementsByTagNameNS($namespace, 'Body');
            if ($nodeListBody->length == 0) {
                $root->appendChild($header);
            } else {
                $body = $nodeListBody->item(0);
                $header = $body->parentNode->insertBefore($header, $body);
            }
            $header->appendChild($node);
        } else {
            $nodeListHeader->item(0)->appendChild($node);
        }
    }

    /**
     * Add new soap body element.
     *
     * @param \DOMElement $node
     * @return void
     */
    public function addBodyElement(\DOMElement $node)
    {
        $root = $this->domDocument->documentElement;
        $namespace = $root->namespaceURI;
        $prefix = $root->prefix;
        $nodeList = $this->domDocument->getElementsByTagNameNS($namespace, 'Body');
        // add body if not there
        if ($nodeList->length == 0) {
            // new body element
            $body = $this->domDocument->createElementNS($namespace, $prefix . ':Body');
            $root->appendChild($body);
            $body->appendChild($node);
        } else {
            $nodeList->item(0)->appendChild($node);
        }
    }

    /**
     * Add new namespace to root tag.
     *
     * @param string $prefix
     * @param string $namespaceURI
     * @return void
     */
    public function addNamespace($prefix, $namespaceURI)
    {
        if (!isset($this->namespaces[$namespaceURI])) {
            $root = $this->domDocument->documentElement;
            $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . $prefix, $namespaceURI);
            $this->namespaces[$namespaceURI] = $prefix;
        }
    }

    /**
     * Create new element for given namespace.
     *
     * @param string $namespaceURI
     * @param string $name
     * @param string $value
     * @return \DOMElement
     */
    public function createElement($namespaceURI, $name, $value = null)
    {
        $prefix = $this->namespaces[$namespaceURI];

        return $this->domDocument->createElementNS($namespaceURI, $prefix . ':' . $name, $value);
    }

    /**
     * Add new attribute to element with given namespace.
     *
     * @param \DOMElement $element
     * @param string      $namespaceURI
     * @param string      $name
     * @param string      $value
     * @return void
     */
    public function setAttribute(\DOMElement $element, $namespaceURI = null, $name, $value)
    {
        if (null !== $namespaceURI) {
            $prefix = $this->namespaces[$namespaceURI];
            $element->setAttributeNS($namespaceURI, $prefix . ':' . $name, $value);
        } else {
            $element->setAttribute($name, $value);
        }
    }

    /**
     * Register namespace.
     *
     * @param string $prefix
     * @param string $namespaceURI
     * @return void
     */
    public function registerNamespace($prefix, $namespaceURI)
    {
        if (!isset($this->namespaces[$namespaceURI])) {
            $this->namespaces[$namespaceURI] = $prefix;
        }
    }
}