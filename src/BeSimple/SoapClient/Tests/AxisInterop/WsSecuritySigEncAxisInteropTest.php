<?php

/*
* Deploy "axis_services/library-signencr.aar" to Apache Axis2 to get this
* example to work.
*
* Links:
* http://www.dcc.uchile.cl/~pcamacho/tutorial/web/xmlsec/xmlsec.html
* http://www.aleksey.com/xmlsec/xmldsig-verifier.html
*
* Using code from axis example:
* http://www.ibm.com/developerworks/java/library/j-jws5/index.html
*
* Download key tool to export private key
* http://couchpotato.net/pkeytool/
*
* keytool -export -alias serverkey -keystore server.keystore -storepass nosecret -file servercert.cer
* openssl x509 -out servercert.pem -outform pem -in servercert.pem -inform der
*
* keytool -export -alias clientkey -keystore client.keystore -storepass nosecret -file clientcert.cer
* openssl x509 -out clientcert.pem -outform pem -in clientcert.pem -inform der
* java -jar pkeytool.jar -exportkey -keystore client.keystore -storepass nosecret -keypass clientpass -rfc -alias clientkey -file clientkey.pem
*
* C:\Program Files\Java\jre6\bin\keytool -export -alias serverkey -keystore server.keystore -storepass nosecret -file servercert.cer
* C:\xampp\apache\bin\openssl x509 -out servercert.pem -outform pem -in servercert.cer -inform der
*
* C:\Program Files\Java\jre6\bin\keytool -export -alias clientkey -keystore client.keystore -storepass nosecret -file clientcert.cer
* C:\xampp\apache\bin\openssl x509 -out clientcert.pem -outform pem -in clientcert.cer -inform der
* java -jar C:\axis2\pkeytool\pkeytool.jar -exportkey -keystore client.keystore -storepass nosecret -keypass clientpass -rfc -alias clientkey -file clientkey.pem
*
* build.properties:
* server-policy=hash-policy-server.xml
*
* allows both text and digest!
*/

use ass\XmlSecurity\Key as XmlSecurityKey;

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\WsSecurityFilter as BeSimpleWsSecurityFilter;
use BeSimple\SoapCommon\WsSecurityKey as BeSimpleWsSecurityKey;

use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBook;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBookResponse;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBooksByType;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBooksByTypeResponse;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\addBook;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\addBookResponse;
use BeSimple\SoapClient\Tests\AxisInterop\Fixtures\BookInformation;

use BeSimple\SoapClient\Tests\AxisInterop\TestCase;

class WsSecuritySigEncAxisInteropTest extends TestCase
{
    private $options = array(
        'soap_version' => SOAP_1_2,
        'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
        'classmap'        => array(
            'getBook'                => 'BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBook',
            'getBookResponse'        => 'BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBookResponse',
            'getBooksByType'         => 'BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBooksByType',
            'getBooksByTypeResponse' => 'BeSimple\SoapClient\Tests\AxisInterop\Fixtures\getBooksByTypeResponse',
            'addBook'                => 'BeSimple\SoapClient\Tests\AxisInterop\Fixtures\addBook',
            'addBookResponse'        => 'BeSimple\SoapClient\Tests\AxisInterop\Fixtures\addBookResponse',
            'BookInformation'        => 'BeSimple\SoapClient\Tests\AxisInterop\Fixtures\BookInformation',
        ),
        'proxy_host' => false,
    );

    public function testSigEnc()
    {
        $sc = new BeSimpleSoapClient(__DIR__.'/Fixtures/WsSecuritySigEnc.wsdl', $this->options);

        $wssFilter = new BeSimpleWsSecurityFilter();
        // user key for signature and encryption
        $securityKeyUser = new BeSimpleWsSecurityKey();
        $securityKeyUser->addPrivateKey(XmlSecurityKey::RSA_SHA1, __DIR__.'/Fixtures/clientkey.pem', true);
        $securityKeyUser->addPublicKey(XmlSecurityKey::RSA_SHA1, __DIR__.'/Fixtures/clientcert.pem', true);
        $wssFilter->setUserSecurityKeyObject($securityKeyUser);
        // service key for encryption
        $securityKeyService = new BeSimpleWsSecurityKey();
        $securityKeyService->addPrivateKey(XmlSecurityKey::TRIPLEDES_CBC);
        $securityKeyService->addPublicKey(XmlSecurityKey::RSA_1_5, __DIR__.'/Fixtures/servercert.pem', true);
        $wssFilter->setServiceSecurityKeyObject($securityKeyService);
        // TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER | TOKEN_REFERENCE_SECURITY_TOKEN | TOKEN_REFERENCE_THUMBPRINT_SHA1
        $wssFilter->setSecurityOptionsSignature(BeSimpleWsSecurityFilter::TOKEN_REFERENCE_SECURITY_TOKEN);
        $wssFilter->setSecurityOptionsEncryption(BeSimpleWsSecurityFilter::TOKEN_REFERENCE_THUMBPRINT_SHA1);

        $soapKernel = $sc->getSoapKernel();
        $soapKernel->registerFilter($wssFilter);

        $gb = new getBook();
        $gb->isbn = '0061020052';
        $result = $sc->getBook($gb);
        $this->assertInstanceOf('BeSimple\SoapClient\Tests\AxisInterop\Fixtures\BookInformation', $result->getBookReturn);

        $ab = new addBook();
        $ab->isbn = '0445203498';
        $ab->title = 'The Dragon Never Sleeps';
        $ab->author = 'Cook, Glen';
        $ab->type = 'scifi';

        $this->assertTrue((bool) $sc->addBook($ab));

        // getBooksByType("scifi");
    }
}
