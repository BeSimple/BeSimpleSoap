<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon;

use ass\XmlSecurity\DSig as XmlSecurityDSig;
use ass\XmlSecurity\Enc as XmlSecurityEnc;
use ass\XmlSecurity\Key as XmlSecurityKey;
use ass\XmlSecurity\Pem as XmlSecurityPem;
use BeSimple\SoapCommon\FilterHelper;
use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\WsSecurityKey;

/**
 * WS-Security common code for client & server.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
abstract class WsSecurityFilterClientServer
{
    /**
     * The date format to be used with {@link \DateTime}
     */
    const DATETIME_FORMAT = 'Y-m-d\TH:i:s.u\Z';

    /**
     * (X509 3.2.1) Reference to a Subject Key Identifier
     */
    const TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER = 0;

    /**
     * (X509 3.2.1) Reference to a Security Token
     */
    const TOKEN_REFERENCE_SECURITY_TOKEN = 1;

    /**
     * (SMS_1.1 7.3) Key Identifiers
     */
    const TOKEN_REFERENCE_THUMBPRINT_SHA1 = 2;

    /**
     * Actor.
     *
     * @var string
     */
    protected $actor;

    /**
     * (SMS 10) Add security timestamp.
     *
     * @var boolean
     */
    protected $addTimestamp;

    /**
     * Encrypt the signature?
     *
     * @var boolean
     */
    protected $encryptSignature;

    /**
     * (SMS 10) Security timestamp expires time in seconds.
     *
     * @var int
     */
    protected $expires;

    /**
     * Sign all headers.
     *
     * @var boolean
     */
    protected $signAllHeaders;

    /**
     * (X509 3.2) Token reference type for encryption.
     *
     * @var int
     */
    protected $tokenReferenceEncryption = null;

    /**
     * (X509 3.2) Token reference type for signature.
     *
     * @var int
     */
    protected $tokenReferenceSignature = null;

    /**
     * Service WsSecurityKey.
     *
     * @var \BeSimple\SoapCommon\WsSecurityKey
     */
    protected $serviceSecurityKey;

    /**
     * User WsSecurityKey.
     *
     * @var \BeSimple\SoapCommon\WsSecurityKey
     */
    protected $userSecurityKey;

    /**
     * Constructor.
     *
     * @param boolean $addTimestamp (SMS 10) Add security timestamp.
     * @param int     $expires      (SMS 10) Security timestamp expires time in seconds.
     * @param string  $actor        SOAP actor
     */
    public function __construct($addTimestamp = true, $expires = 300, $actor = null)
    {
        $this->addTimestamp = $addTimestamp;
        $this->expires      = $expires;
        $this->actor        = $actor;
    }

    /**
     * Reset all properties to default values.
     */
    public function resetFilter()
    {
        $this->actor                    = null;
        $this->addTimestamp             = null;
        $this->encryptSignature         = null;
        $this->expires                  = null;
        $this->serviceSecurityKey       = null;
        $this->signAllHeaders           = null;
        $this->tokenReferenceEncryption = null;
        $this->tokenReferenceSignature  = null;
        $this->userSecurityKey          = null;
    }

    /**
     * Set service security key.
     *
     * @param \BeSimple\SoapCommon\WsSecurityKey $serviceSecurityKey Service security key
     *
     * @return void
     */
    public function setServiceSecurityKeyObject(WsSecurityKey $serviceSecurityKey)
    {
        $this->serviceSecurityKey = $serviceSecurityKey;
    }

    /**
     * Set user security key.
     *
     * @param \BeSimple\SoapCommon\WsSecurityKey $userSecurityKey User security key
     *
     * @return void
     */
    public function setUserSecurityKeyObject(WsSecurityKey $userSecurityKey)
    {
        $this->userSecurityKey = $userSecurityKey;
    }

    /**
     * Set security options.
     *
     * @param int     $tokenReference   self::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER | self::TOKEN_REFERENCE_SECURITY_TOKEN | self::TOKEN_REFERENCE_THUMBPRINT_SHA1
     * @param boolean $encryptSignature Encrypt signature
     *
     * @return void
     */
    public function setSecurityOptionsEncryption($tokenReference, $encryptSignature = false)
    {
        $this->tokenReferenceEncryption = $tokenReference;
        $this->encryptSignature         = $encryptSignature;
    }

    /**
     * Set security options.
     *
     * @param int     $tokenReference self::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER | self::TOKEN_REFERENCE_SECURITY_TOKEN | self::TOKEN_REFERENCE_THUMBPRINT_SHA1
     * @param boolean $signAllHeaders Sign all headers?
     *
     * @return void
     */
    public function setSecurityOptionsSignature($tokenReference, $signAllHeaders = false)
    {
        $this->tokenReferenceSignature = $tokenReference;
        $this->signAllHeaders          = $signAllHeaders;
    }

    /**
     * Adds the configured KeyInfo to the parentNode.
     *
     * @param FilterHelper         $filterHelper   Filter helper object
     * @param int                  $tokenReference Token reference type
     * @param string               $guid           Unique ID
     * @param \ass\XmlSecurity\Key $xmlSecurityKey XML security key
     *
     * @return \DOMElement
     */
    protected function createKeyInfo(FilterHelper $filterHelper, $tokenReference, $guid, XmlSecurityKey $xmlSecurityKey = null)
    {
        $keyInfo = $filterHelper->createElement(XmlSecurityDSig::NS_XMLDSIG, 'KeyInfo');
        $securityTokenReference = $filterHelper->createElement(Helper::NS_WSS, 'SecurityTokenReference');
        $keyInfo->appendChild($securityTokenReference);
        // security token
        if (self::TOKEN_REFERENCE_SECURITY_TOKEN === $tokenReference) {
            $reference = $filterHelper->createElement(Helper::NS_WSS, 'Reference');
            $filterHelper->setAttribute($reference, null, 'URI', '#' . $guid);
            if (null !== $xmlSecurityKey) {
                $filterHelper->setAttribute($reference, null, 'ValueType', Helper::NAME_WSS_X509 . '#X509v3');
            }
            $securityTokenReference->appendChild($reference);
        // subject key identifier
        } elseif (self::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER === $tokenReference && null !== $xmlSecurityKey) {
            $keyIdentifier = $filterHelper->createElement(Helper::NS_WSS, 'KeyIdentifier');
            $filterHelper->setAttribute($keyIdentifier, null, 'EncodingType', Helper::NAME_WSS_SMS . '#Base64Binary');
            $filterHelper->setAttribute($keyIdentifier, null, 'ValueType', Helper::NAME_WSS_X509 . '#509SubjectKeyIdentifier');
            $securityTokenReference->appendChild($keyIdentifier);
            $certificate = $xmlSecurityKey->getX509SubjectKeyIdentifier();
            $dataNode = new \DOMText($certificate);
            $keyIdentifier->appendChild($dataNode);
        // thumbprint sha1
        } elseif (self::TOKEN_REFERENCE_THUMBPRINT_SHA1 === $tokenReference && null !== $xmlSecurityKey) {
            $keyIdentifier = $filterHelper->createElement(Helper::NS_WSS, 'KeyIdentifier');
            $filterHelper->setAttribute($keyIdentifier, null, 'EncodingType', Helper::NAME_WSS_SMS . '#Base64Binary');
            $filterHelper->setAttribute($keyIdentifier, null, 'ValueType', Helper::NAME_WSS_SMS_1_1 . '#ThumbprintSHA1');
            $securityTokenReference->appendChild($keyIdentifier);
            $thumbprintSha1 = base64_encode(sha1(base64_decode($xmlSecurityKey->getX509Certificate(true)), true));
            $dataNode = new \DOMText($thumbprintSha1);
            $keyIdentifier->appendChild($dataNode);
        }

        return $keyInfo;
    }

    /**
     * Create a list of \DOMNodes that should be encrypted.
     *
     * @param \DOMDocument $dom      DOMDocument to query
     *
     * @return \DOMNodeList
     */
    protected function createNodeListForEncryption(\DOMDocument $dom)
    {
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('SOAP-ENV', $dom->documentElement->namespaceURI);
        $xpath->registerNamespace('ds', XmlSecurityDSig::NS_XMLDSIG);
        if ($this->encryptSignature === true) {
            $query = '//ds:Signature | //SOAP-ENV:Body';
        } else {
            $query = '//SOAP-ENV:Body';
        }

        return $xpath->query($query);
    }

    /**
     * Create a list of \DOMNodes that should be signed.
     *
     * @param \DOMDocument $dom      DOMDocument to query
     * @param \DOMElement  $security Security element
     *
     * @return array(\DOMNode)
     */
    protected function createNodeListForSigning(\DOMDocument $dom, \DOMElement $security)
    {
        $nodes = array();
        $body = $dom->getElementsByTagNameNS($dom->documentElement->namespaceURI, 'Body')->item(0);
        if (null !== $body) {
            $nodes[] = $body;
        }
        foreach ($security->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                $nodes[] = $node;
            }
        }
        if ($this->signAllHeaders) {
            foreach ($security->parentNode->childNodes as $node) {
                if (XML_ELEMENT_NODE === $node->nodeType &&
                    Helper::NS_WSS !== $node->namespaceURI) {
                    $nodes[] = $node;
                }
            }
        }

        return $nodes;
    }

    /**
     * Gets the referenced node for the given URI.
     *
     * @param \DOMElement $node Node
     * @param string      $uri  URI
     *
     * @return \DOMElement
     */
    protected function getReferenceNodeForUri(\DOMElement $node, $uri)
    {
        $url = parse_url($uri);
        $referenceId = $url['fragment'];
        $query = '//*[@'.Helper::PFX_WSU.':Id="'.$referenceId.'" or @Id="'.$referenceId.'"]';
        $xpath = new \DOMXPath($node->ownerDocument);
        $xpath->registerNamespace(Helper::PFX_WSU, Helper::NS_WSU);

        return $xpath->query($query)->item(0);
    }


    /**
     * Tries to resolve a key from the given \DOMElement.
     *
     * @param \DOMElement $node      Node where to resolve the key
     * @param string      $algorithm XML security key algorithm
     *
     * @return \ass\XmlSecurity\Key|null
     */
    public function keyInfoSecurityTokenReferenceResolver(\DOMElement $node, $algorithm)
    {
        foreach ($node->childNodes as $key) {
            if (Helper::NS_WSS === $key->namespaceURI) {
                switch ($key->localName) {
                    case 'KeyIdentifier':

                        return $this->serviceSecurityKey->getPublicKey();
                    case 'Reference':
                        $uri = $key->getAttribute('URI');
                        $referencedNode = $this->getReferenceNodeForUri($node, $uri);

                        if (XmlSecurityEnc::NS_XMLENC === $referencedNode->namespaceURI
                                && 'EncryptedKey' == $referencedNode->localName) {
                            $key = XmlSecurityEnc::decryptEncryptedKey($referencedNode, $this->userSecurityKey->getPrivateKey());

                            return XmlSecurityKey::factory($algorithm, $key, false, XmlSecurityKey::TYPE_PRIVATE);
                        } elseif (Helper::NS_WSS === $referencedNode->namespaceURI
                                && 'BinarySecurityToken' == $referencedNode->localName) {

                            $key = XmlSecurityPem::formatKeyInPemFormat($referencedNode->textContent);

                            return XmlSecurityKey::factory(XmlSecurityKey::RSA_SHA1, $key, false, XmlSecurityKey::TYPE_PUBLIC);
                        }
                }
            }
        }

        return null;
    }
}
