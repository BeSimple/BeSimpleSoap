<?php
/**
 * This file is part of the BeSimpleSoapClient.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 * (c) Andreas Schamberger <mail@andreass.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 * 
 * @link https://github.com/BeSimple/BeSimpleSoapClient
 */

namespace BeSimple\SoapClient;

/**
 * Soap helper class with static functions that are used in the client and
 * server implementations. It also provides namespace and configuration
 * constants.
 *
 * @author Andreas Schamberger
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
     * Constant for a request.
     */
    const REQUEST = 0;

    /**
     * Constant for a response.
     */
    const RESPONSE = 1;

    /**
     * Wheather to format the XML output or not.
     *
     * @var boolean
     */
    public static $formatXmlOutput = false;

    /**
     * Contains previously defined error handler string.
     *
     * @var string
     */
    private static $previousErrorHandler = null;

    /**
     * User-defined error handler function to convert errors to exceptions.
     *
     * @param string $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @throws ErrorException
     */
    public static function exceptionErrorHandler( $errno, $errstr, $errfile, $errline )
    {
        // don't throw exception for errors suppresed with @
        if ( error_reporting() != 0 )
        {
            throw new \ErrorException( $errstr, 0, $errno, $errfile, $errline );
        }
    }

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
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * Builds the current URL from the $_SERVER array.
     *
     * @return string
     */
    public static function getCurrentUrl()
    {
        $url = '';
        if ( isset( $_SERVER['HTTPS'] ) &&
             ( strtolower( $_SERVER['HTTPS'] ) === 'on' || $_SERVER['HTTPS'] === '1' ) )
        {
            $url .= 'https://';
        }
        else
        {
            $url .= 'http://';
        }
        $url .= isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : '';
        if ( isset( $_SERVER['SERVER_PORT'] ) && $_SERVER['SERVER_PORT'] != 80 )
        {
            $url .= ":{$_SERVER['SERVER_PORT']}";
        }
        $url .= isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
        return $url;
    }

    /**
     * Get SOAP namespace for the given $version.
     *
     * @param int $version SOAP_1_1|SOAP_1_2
     * @return string
     */
    public static function getSoapNamespace( $version )
    {
        if ( $version === SOAP_1_2 )
        {
            return self::NS_SOAP_1_2;
        }
        else
        {
            return self::NS_SOAP_1_1;
        }
    }

    /**
     * Get SOAP version from namespace URI.
     *
     * @param string $namespace NS_SOAP_1_1|NS_SOAP_1_2
     * @return int SOAP_1_1|SOAP_1_2
     */
    public static function getSoapVersionFromNamespace( $namespace )
    {
        if ( $namespace === self::NS_SOAP_1_2 )
        {
            return SOAP_1_2;
        }
        else
        {
            return SOAP_1_1;
        }
    }

    /**
     * Runs the registered Plugins on the given request $xml.
     *
     * @param array(\ass\Soap\Plugin) $plugins
     * @param int $requestType \ass\Soap\Helper::REQUEST|\ass\Soap\Helper::RESPONSE
     * @param string $xml
     * @param string $location
     * @param string $action
     * @param \ass\Soap\WsdlHandler $wsdlHandler
     * @return string
     */
    public static function runPlugins( array $plugins, $requestType, $xml, $location = null, $action = null, \ass\Soap\WsdlHandler $wsdlHandler = null )
    {
        if ( count( $plugins ) > 0 )
        {
            // instantiate new dom object
            $dom = new \DOMDocument( '1.0' );
            // format the XML if option is set
            $dom->formatOutput = self::$formatXmlOutput;
            $dom->loadXML( $xml );
            $params = array(
                $dom,
                $location,
                $action,
                $wsdlHandler
            );
            if ( $requestType == self::REQUEST )
            {
                $callMethod = 'modifyRequest';
            }
            else
            {
                $callMethod = 'modifyResponse';
            }
            // modify dom
            foreach( $plugins AS $plugin )
            {
                if ( $plugin instanceof \ass\Soap\Plugin )
                {
                    call_user_func_array( array( $plugin, $callMethod ), $params );
                }
            }
            // return the modified xml document
            return $dom->saveXML();
        }
        // format the XML if option is set
        elseif ( self::$formatXmlOutput === true )
        {
            $dom = new \DOMDocument( '1.0' );
            $dom->formatOutput = true;
            $dom->loadXML( $xml );
            return $dom->saveXML();
        }
        return $xml;
    }

    /**
     * Set custom error handler that converts all php errors to ErrorExceptions
     *
     * @param boolean $reset
     */
    public static function setCustomErrorHandler( $reset = false )
    {
        if ( $reset === true && !is_null( self::$previousErrorHandler ) )
        {
            set_error_handler( self::$previousErrorHandler );
            self::$previousErrorHandler = null;
        }
        else
        {
            self::$previousErrorHandler = set_error_handler( 'ass\\Soap\\Helper::exceptionErrorHandler' );
        }
        return self::$previousErrorHandler;
    }

    /**
     * Build data string to used to bridge ext/soap
     * 'SOAP_MIME_ATTACHMENT:cid=...&type=...'
     *
     * @param string $contentId
     * @param string $contentType
     * @return string
     */
    public static function makeSoapAttachmentDataString( $contentId, $contentType )
    {
        $parameter = array(
            'cid' => $contentId,
            'type' => $contentType,
        );
        return 'SOAP-MIME-ATTACHMENT:' . http_build_query( $parameter, null, '&' );
    }

    /**
     * Parse data string used to bridge ext/soap
     * 'SOAP_MIME_ATTACHMENT:cid=...&type=...'
     *
     * @param string $dataString
     * @return array(string=>string)
     */
    public static function parseSoapAttachmentDataString( $dataString )
    {
        $dataString = substr( $dataString, 21 );
        // get all data
        $data = array();
        parse_str( $dataString, $data );
        return $data;
    }

    /**
     * Function to set proper http status header.
     * Neccessary as there is a difference between mod_php and the cgi SAPIs.
     *
     * @param string $header
     */
    public static function setHttpStatusHeader( $header )
    {
        if ( substr( php_sapi_name(), 0, 3 ) == 'cgi' )
        {
            header( 'Status: ' . $header );
        }
        else
        {
            header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $header );
        }
    }
}