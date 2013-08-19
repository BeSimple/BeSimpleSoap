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

    }

}