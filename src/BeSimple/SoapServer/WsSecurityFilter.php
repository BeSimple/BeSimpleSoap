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
use BeSimple\SoapCommon\FilterHelper;
use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\SoapRequest as CommonSoapRequest;
use BeSimple\SoapCommon\SoapRequestFilter;
use BeSimple\SoapCommon\SoapResponse as CommonSoapResponse;
use BeSimple\SoapCommon\SoapResponseFilter;
use BeSimple\SoapCommon\WsSecurityFilterClientServer;

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
class WsSecurityFilter extends WsSecurityFilterClientServer implements SoapRequestFilter, SoapResponseFilter
{
    /**
     * Username/password callback that returns password or null.
     *
     * @var callable
     */
    protected $usernamePasswordCallback;

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
        parent::resetFilter();
        $this->usernamePasswordCallback = null;
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
                $expiresDatetime = \DateTime::createFromFormat(static::DATETIME_FORMAT, $expires->textContent, new \DateTimeZone('UTC'));
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

            // do we have a reference list in header
            $referenceList = XmlSecurityEnc::locateReferenceList($security);
            // get a list of encrypted nodes
            $encryptedNodes = XmlSecurityEnc::locateEncryptedData($dom, $referenceList);
            // decrypt them
            if (null !== $encryptedNodes) {
                foreach ($encryptedNodes as $encryptedNode) {
                    XmlSecurityEnc::decryptNode($encryptedNode);
                }
            }

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
        $createdTimestamp = $dt->format(static::DATETIME_FORMAT);

        // create security header
        $security = $filterHelper->createElement(Helper::NS_WSS, 'Security');
        $filterHelper->addHeaderElement($security, true, $this->actor, $response->getVersion());

        if (true === $this->addTimestamp || null !== $this->expires) {
            $timestamp = $filterHelper->createElement(Helper::NS_WSU, 'Timestamp');
            $created = $filterHelper->createElement(Helper::NS_WSU, 'Created', $createdTimestamp);
            $timestamp->appendChild($created);
            if (null !== $this->expires) {
                $dt->modify('+' . $this->expires . ' seconds');
                $expiresTimestamp = $dt->format(static::DATETIME_FORMAT);
                $expires = $filterHelper->createElement(Helper::NS_WSU, 'Expires', $expiresTimestamp);
                $timestamp->appendChild($expires);
            }
            $security->appendChild($timestamp);
        }

        if (null !== $this->userSecurityKey && $this->userSecurityKey->hasKeys()) {
            $guid = 'CertId-' . Helper::generateUUID();
            // add token references
            $keyInfo = null;
            if (null !== $this->tokenReferenceSignature) {
                $keyInfo = $this->createKeyInfo($filterHelper, $this->tokenReferenceSignature, $guid, $this->userSecurityKey->getPublicKey());
            }
            $nodes = $this->createNodeListForSigning($dom, $security);
            $signature = XmlSecurityDSig::createSignature($this->userSecurityKey->getPrivateKey(), XmlSecurityDSig::EXC_C14N, $security, null, $keyInfo);
            $options = array(
                'id_ns_prefix' => Helper::PFX_WSU,
                'id_prefix_ns' => Helper::NS_WSU,
            );
            foreach ($nodes as $node) {
                XmlSecurityDSig::addNodeToSignature($signature, $node, XmlSecurityDSig::SHA1, XmlSecurityDSig::EXC_C14N, $options);
            }
            XmlSecurityDSig::signDocument($signature, $this->userSecurityKey->getPrivateKey(), XmlSecurityDSig::EXC_C14N);

            $publicCertificate = $this->userSecurityKey->getPublicKey()->getX509Certificate(true);
            $binarySecurityToken = $filterHelper->createElement(Helper::NS_WSS, 'BinarySecurityToken', $publicCertificate);
            $filterHelper->setAttribute($binarySecurityToken, null, 'EncodingType', Helper::NAME_WSS_SMS . '#Base64Binary');
            $filterHelper->setAttribute($binarySecurityToken, null, 'ValueType', Helper::NAME_WSS_X509 . '#X509v3');
            $filterHelper->setAttribute($binarySecurityToken, Helper::NS_WSU, 'Id', $guid);
            $security->insertBefore($binarySecurityToken, $signature);

            // encrypt soap document
            if (null !== $this->serviceSecurityKey && $this->serviceSecurityKey->hasKeys()) {
                $guid = 'EncKey-' . Helper::generateUUID();
                // add token references
                $keyInfo = null;
                if (null !== $this->tokenReferenceEncryption) {
                    $keyInfo = $this->createKeyInfo($filterHelper, $this->tokenReferenceEncryption, $guid, $this->serviceSecurityKey->getPublicKey());
                }
                $encryptedKey = XmlSecurityEnc::createEncryptedKey($guid, $this->serviceSecurityKey->getPrivateKey(), $this->serviceSecurityKey->getPublicKey(), $security, $signature, $keyInfo);
                $referenceList = XmlSecurityEnc::createReferenceList($encryptedKey);
                // token reference to encrypted key
                $keyInfo = $this->createKeyInfo($filterHelper, self::TOKEN_REFERENCE_SECURITY_TOKEN, $guid);
                $nodes = $this->createNodeListForEncryption($dom);
                foreach ($nodes as $node) {
                    $type = XmlSecurityEnc::ELEMENT;
                    if ($node->localName == 'Body') {
                        $type = XmlSecurityEnc::CONTENT;
                    }
                    XmlSecurityEnc::encryptNode($node, $type, $this->serviceSecurityKey->getPrivateKey(), $referenceList, $keyInfo);
                }
            }
        }
    }
}
