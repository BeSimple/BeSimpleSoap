<?php

/*
 * This file is part of BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon;

/**
 * Soap helper class with static functions that are used in the client and
 * server implementations. It also provides namespace and configuration
 * constants.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class Helper
{
    /**
     * Attachment type: xsd:base64Binary (native in ext/soap).
     */
    const ATTACHMENTS_TYPE_BASE64 = 1;

    /**
     * Attachment type: MTOM (SOAP Message Transmission Optimization Mechanism).
     */
    const ATTACHMENTS_TYPE_MTOM = 2;

    /**
     * Attachment type: SWA (SOAP Messages with Attachments).
     */
    const ATTACHMENTS_TYPE_SWA = 4;

    /**
     * Web Services Security: SOAP Message Security 1.0 (WS-Security 2004)
     */
    const NAME_WSS_SMS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0';

    /**
     * Web Services Security: SOAP Message Security 1.1 (WS-Security 2004)
     */
    const NAME_WSS_SMS_1_1 = 'http://docs.oasis-open.org/wss/oasis-wss-soap-message-security-1.1';

    /**
     * Web Services Security UsernameToken Profile 1.0
     */
    const NAME_WSS_UTP = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0';

    /**
     * Web Services Security X.509 Certificate Token Profile
     */
    const NAME_WSS_X509 = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0';

    /**
     * Soap 1.1 namespace.
     */
    const NS_SOAP_1_1 = 'http://schemas.xmlsoap.org/soap/envelope/';

    /**
     * Soap 1.1 namespace.
     */
    const NS_SOAP_1_2 = 'http://www.w3.org/2003/05/soap-envelope/';

    /**
     * Web Services Addressing 1.0 namespace.
     */
    const NS_WSA = 'http://www.w3.org/2005/08/addressing';

    /**
     * WSDL 1.1 namespace.
     */
    const NS_WSDL = 'http://schemas.xmlsoap.org/wsdl/';

    /**
     * WSDL MIME namespace.
     */
    const NS_WSDL_MIME = 'http://schemas.xmlsoap.org/wsdl/mime/';

    /**
     * WSDL SOAP 1.1 namespace.
     */
    const NS_WSDL_SOAP_1_1 = 'http://schemas.xmlsoap.org/wsdl/soap/';

    /**
     * WSDL SOAP 1.2 namespace.
     */
    const NS_WSDL_SOAP_1_2 = 'http://schemas.xmlsoap.org/wsdl/soap12/';

    /**
     * Web Services Security Extension namespace.
     */
    const NS_WSS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * Web Services Security Utility namespace.
     */
    const NS_WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

    /**
     * Describing Media Content of Binary Data in XML namespace.
     */
    const NS_XMLMIME = 'http://www.w3.org/2004/11/xmlmime';

    /**
     * XML Schema namespace.
     */
    const NS_XML_SCHEMA = 'http://www.w3.org/2001/XMLSchema';

    /**
     * XML Schema instance namespace.
     */
    const NS_XML_SCHEMA_INSTANCE = 'http://www.w3.org/2001/XMLSchema-instance';

    /**
     * XML-binary Optimized Packaging namespace.
     */
    const NS_XOP = 'http://www.w3.org/2004/08/xop/include';

    /**
     * Web Services Addressing 1.0 prefix.
     */
    const PFX_WSA = 'wsa';

    /**
     * WSDL 1.1 namespace. prefix.
     */
    const PFX_WSDL = 'wsdl';

    /**
     * Web Services Security Extension namespace.
     */
    const PFX_WSS = 'wsse';

    /**
     * Web Services Security Utility namespace prefix.
     */
    const PFX_WSU  = 'wsu';

    /**
     * Describing Media Content of Binary Data in XML namespace prefix.
     */
    const PFX_XMLMIME = 'xmlmime';

    /**
     * XML Schema namespace prefix.
     */
    const PFX_XML_SCHEMA = 'xsd';

    /**
     * XML Schema instance namespace prefix.
     */
    const PFX_XML_SCHEMA_INSTANCE = 'xsi';

    /**
     * XML-binary Optimized Packaging namespace prefix.
     */
    const PFX_XOP = 'xop';

    /**
     * Generate a pseudo-random version 4 UUID.
     *
     * @see http://de.php.net/manual/en/function.uniqid.php#94959
     * @return string
     */
    public static function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get SOAP namespace for the given $version.
     *
     * @param int $version SOAP_1_1|SOAP_1_2
     *
     * @return string
     */
    public static function getSoapNamespace($version)
    {
        if ($version === SOAP_1_2) {
            return self::NS_SOAP_1_2;
        } else {
            return self::NS_SOAP_1_1;
        }
    }

    /**
     * Get SOAP version from namespace URI.
     *
     * @param string $namespace NS_SOAP_1_1|NS_SOAP_1_2
     *
     * @return int SOAP_1_1|SOAP_1_2
     */
    public static function getSoapVersionFromNamespace($namespace)
    {
        if ($namespace === self::NS_SOAP_1_2) {
            return SOAP_1_2;
        } else {
            return SOAP_1_1;
        }
    }
}