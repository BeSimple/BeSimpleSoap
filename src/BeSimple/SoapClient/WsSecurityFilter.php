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
     * (UT 3.1) Password type: plain text.
     */
    const PASSWORD_TYPE_TEXT = 0;

    /**
     * (UT 3.1) Password type: digest.
     */
    const PASSWORD_TYPE_DIGEST = 1;

    /**
     * (UT 3.1) Password.
     *
     * @var string
     */
    protected $password;

    /**
     * (UT 3.1) Password type: text or digest.
     *
     * @var int
     */
    protected $passwordType;

    /**
     * (UT 3.1) Username.
     *
     * @var string
     */
    protected $username;

    /**
     * User WsSecurityKey.
     *
     * @var \BeSimple\SoapCommon\WsSecurityKey
     */
    protected $userSecurityKey;

    /**
     * Add user data.
     *
     * @param string $username     Username
     * @param string $password     Password
     * @param int    $passwordType self::PASSWORD_TYPE_DIGEST | self::PASSWORD_TYPE_TEXT
     *
     * @return void
     */
    public function addUserData($username, $password = null, $passwordType = self::PASSWORD_TYPE_DIGEST)
    {
        $this->username     = $username;
        $this->password     = $password;
        $this->passwordType = $passwordType;
    }

    /**
     * Reset all properties to default values.
     */
    public function resetFilter()
    {
        parent::resetFilter();
        $this->password     = null;
        $this->passwordType = null;
        $this->username     = null;
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
        $filterHelper->addHeaderElement($security, true, $this->actor, $request->getVersion());

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

        if (null !== $this->username) {
            $usernameToken = $filterHelper->createElement(Helper::NS_WSS, 'UsernameToken');
            $security->appendChild($usernameToken);

            $username = $filterHelper->createElement(Helper::NS_WSS, 'Username', $this->username);
            $usernameToken->appendChild($username);

            if (null !== $this->password
                && (null === $this->userSecurityKey
                    || (null !== $this->userSecurityKey && !$this->userSecurityKey->hasPrivateKey()))) {

                if (self::PASSWORD_TYPE_DIGEST === $this->passwordType) {
                    $nonce = mt_rand();
                    $password = base64_encode(sha1($nonce . $createdTimestamp . $this->password, true));
                    $passwordType = Helper::NAME_WSS_UTP . '#PasswordDigest';
                } else {
                    $password = $this->password;
                    $passwordType = Helper::NAME_WSS_UTP . '#PasswordText';
                }
                $password = $filterHelper->createElement(Helper::NS_WSS, 'Password', $password);
                $filterHelper->setAttribute($password, null, 'Type', $passwordType);
                $usernameToken->appendChild($password);
                if (self::PASSWORD_TYPE_DIGEST === $this->passwordType) {
                    $nonce = $filterHelper->createElement(Helper::NS_WSS, 'Nonce', base64_encode($nonce));
                    $usernameToken->appendChild($nonce);

                    $created = $filterHelper->createElement(Helper::NS_WSU, 'Created', $createdTimestamp);
                    $usernameToken->appendChild($created);
                }
            }
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

        // locate security header
        $security = $dom->getElementsByTagNameNS(Helper::NS_WSS, 'Security')->item(0);
        if (null !== $security) {
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
                if (XmlSecurityDSig::getSecurityKey($signature) !== null) {
                    if (XmlSecurityDSig::verifyDocumentSignature($signature) !== true) {
                        throw new \SoapFault('wsse:FailedCheck', 'The signature or decryption was invalid');
                    }
                }
            }

            $security->parentNode->removeChild($security);
        }
    }
}
