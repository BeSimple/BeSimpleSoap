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

use ass\XmlSecurity\DSig as XmlSecurityDSig;
use ass\XmlSecurity\Enc as XmlSecurityEnc;
use ass\XmlSecurity\Key as XmlSecurityKey;
use ass\XmlSecurity\Pem as XmlSecurityPem;

use BeSimple\SoapCommon\FilterHelper;
use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\SoapRequest as CommonSoapRequest;
use BeSimple\SoapCommon\SoapRequestFilter;
use BeSimple\SoapCommon\SoapResponse as CommonSoapResponse;
use BeSimple\SoapCommon\SoapResponseFilter;
use BeSimple\SoapCommon\WsSecurityKey;

/**
 * This plugin implements a subset of the following standards:
 *  * Web Services Security: SOAP Message Security 1.0 (WS-Security 2004)
 *      http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0.pdf
 *  * Web Services Security UsernameToken Profile 1.0
 *      http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0.pdf
 *  * Web Services Security X.509 Certificate Token Profile
 *      http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0.pdf
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class WsSecurityFilter implements SoapRequestFilter, SoapResponseFilter
{
    /*
     * The date format to be used with {@link \DateTime}
     */
    const DATETIME_FORMAT = 'Y-m-d\TH:i:s.000\Z';

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
     * Username/password callback that returns password or null.
     *
     * @var callable
     */
    protected $usernamePasswordCallback;

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
     * Set username/password callback that returns password or null.
     *
     * @param callable $callback Username/password callback function
     *
     * @return void
     */
    public function setUsernamePasswordCallback($callback)
    {
        $this->usernamePasswordCallback = $callback;
    }

    /**
     * Reset all properties to default values.
     */
    public function resetFilter()
    {
        $this->actor                    = null;
        $this->addTimestamp             = null;
        $this->expires                  = null;
        $this->serviceSecurityKey       = null;
        $this->signAllHeaders           = null;
        $this->tokenReferenceEncryption = null;
        $this->tokenReferenceSignature  = null;
        $this->usernamePasswordCallback = null;
    }

    /**
     * Get service security key.
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
     * Modify the given request XML.
     *
     * @param \BeSimple\SoapCommon\SoapRequest $request SOAP request
     *
     * @return void
     */
    public function filterRequest(CommonSoapRequest $request)
    {
        // get \DOMDocument from SOAP request
        $dom = $request->getContentDocument();

        // locate security header
        $security = $dom->getElementsByTagNameNS(Helper::NS_WSS, 'Security')->item(0);
        if (null !== $security) {

            // is security header still valid?
            $query = '//'.Helper::PFX_WSU.':Timestamp/'.Helper::PFX_WSU.':Expires';
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace(Helper::PFX_WSU, Helper::NS_WSU);
            $expires = $xpath->query($query, $security)->item(0);

            if (null !== $expires) {
                $expiresDatetime = \DateTime::createFromFormat(self::DATETIME_FORMAT, $expires->textContent, new \DateTimeZone('UTC'));
                $currentDatetime = new \DateTime('now', new \DateTimeZone('UTC'));

                if ($currentDatetime > $expiresDatetime) {
                    throw new \SoapFault('wsu:MessageExpired', 'Security semantics are expired');
                }
            }

            $usernameToken = $security->getElementsByTagNameNS(Helper::NS_WSS, 'UsernameToken')->item(0);
            if (null !== $usernameToken) {
                $usernameTokenUsername = $usernameToken->getElementsByTagNameNS(Helper::NS_WSS, 'Username')->item(0);
                $usernameTokenPassword = $usernameToken->getElementsByTagNameNS(Helper::NS_WSS, 'Password')->item(0);

                $password = call_user_func($this->usernamePasswordCallback, $usernameTokenUsername->textContent);

                if ($usernameTokenPassword->getAttribute('Type') == Helper::NAME_WSS_UTP . '#PasswordDigest') {
                    $nonce = $usernameToken->getElementsByTagNameNS(Helper::NS_WSS, 'Nonce')->item(0);
                    $created = $usernameToken->getElementsByTagNameNS(Helper::NS_WSU, 'Created')->item(0);
                    $password = base64_encode(sha1(base64_decode($nonce->textContent) . $created->textContent . $password, true));
                }

                if (null === $password || $usernameTokenPassword->textContent != $password) {
                    throw new \SoapFault('wsse:FailedAuthentication', 'The security token could not be authenticated or authorized');
                }
            }

            // add SecurityTokenReference resolver for KeyInfo
            $keyResolver = array($this, 'keyInfoSecurityTokenReferenceResolver');
            XmlSecurityDSig::addKeyInfoResolver(Helper::NS_WSS, 'SecurityTokenReference', $keyResolver);

            // locate signature node
            $signature = XmlSecurityDSig::locateSignature($security);
            if (null !== $signature) {
                // verify references
                $options = array(
                    'id_ns_prefix' => Helper::PFX_WSU,
                    'id_prefix_ns' => Helper::NS_WSU,
                );
                if (XmlSecurityDSig::verifyReferences($signature, $options) !== true) {
                    throw new \SoapFault('wsse:FailedCheck', 'The signature or decryption was invalid');
                }
                // verify signature
                if (XmlSecurityDSig::verifyDocumentSignature($signature) !== true) {
                    throw new \SoapFault('wsse:FailedCheck', 'The signature or decryption was invalid');
                }
            }

            $security->parentNode->removeChild($security);
        }
    }

    /**
     * Modify the given request XML.
     *
     * @param \BeSimple\SoapCommon\SoapResponse $response SOAP response
     *
     * @return void
     */
    public function filterResponse(CommonSoapResponse $response)
    {
        // get \DOMDocument from SOAP response
        $dom = $response->getContentDocument();

        // create FilterHelper
        $filterHelper = new FilterHelper($dom);

        // add the neccessary namespaces
        $filterHelper->addNamespace(Helper::PFX_WSS, Helper::NS_WSS);
        $filterHelper->addNamespace(Helper::PFX_WSU, Helper::NS_WSU);
        $filterHelper->registerNamespace(XmlSecurityDSig::PFX_XMLDSIG, XmlSecurityDSig::NS_XMLDSIG);

        // init timestamp
        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        $createdTimestamp = $dt->format(self::DATETIME_FORMAT);

        // create security header
        $security = $filterHelper->createElement(Helper::NS_WSS, 'Security');
        $filterHelper->addHeaderElement($security, true, $this->actor, $response->getVersion());

        if (true === $this->addTimestamp || null !== $this->expires) {
            $timestamp = $filterHelper->createElement(Helper::NS_WSU, 'Timestamp');
            $created = $filterHelper->createElement(Helper::NS_WSU, 'Created', $createdTimestamp);
            $timestamp->appendChild($created);
            if (null !== $this->expires) {
                $dt->modify('+' . $this->expires . ' seconds');
                $expiresTimestamp = $dt->format(self::DATETIME_FORMAT);
                $expires = $filterHelper->createElement(Helper::NS_WSU, 'Expires', $expiresTimestamp);
                $timestamp->appendChild($expires);
            }
            $security->appendChild($timestamp);
        }

        if (null !== $this->serviceSecurityKey && $this->serviceSecurityKey->hasKeys()) {
            $guid = 'CertId-' . Helper::generateUUID();
            // add token references
            $keyInfo = null;
            if (null !== $this->tokenReferenceSignature) {
                $keyInfo = $this->createKeyInfo($filterHelper, $this->tokenReferenceSignature, $guid, $this->serviceSecurityKey->getPublicKey());
            }
            $nodes = $this->createNodeListForSigning($dom, $security);
            $signature = XmlSecurityDSig::createSignature($this->serviceSecurityKey->getPrivateKey(), XmlSecurityDSig::EXC_C14N, $security, null, $keyInfo);
            $options = array(
                'id_ns_prefix' => Helper::PFX_WSU,
                'id_prefix_ns' => Helper::NS_WSU,
            );
            foreach ($nodes as $node) {
                XmlSecurityDSig::addNodeToSignature($signature, $node, XmlSecurityDSig::SHA1, XmlSecurityDSig::EXC_C14N, $options);
            }
            XmlSecurityDSig::signDocument($signature, $this->serviceSecurityKey->getPrivateKey(), XmlSecurityDSig::EXC_C14N);

            $publicCertificate = $this->serviceSecurityKey->getPublicKey()->getX509Certificate(true);
            $binarySecurityToken = $filterHelper->createElement(Helper::NS_WSS, 'BinarySecurityToken', $publicCertificate);
            $filterHelper->setAttribute($binarySecurityToken, null, 'EncodingType', Helper::NAME_WSS_SMS . '#Base64Binary');
            $filterHelper->setAttribute($binarySecurityToken, null, 'ValueType', Helper::NAME_WSS_X509 . '#X509v3');
            $filterHelper->setAttribute($binarySecurityToken, Helper::NS_WSU, 'Id', $guid);
            $security->insertBefore($binarySecurityToken, $signature);
        }

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
                            $key = XmlSecurityEnc::decryptEncryptedKey($referencedNode, $this->serviceSecurityKey->getPrivateKey());

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