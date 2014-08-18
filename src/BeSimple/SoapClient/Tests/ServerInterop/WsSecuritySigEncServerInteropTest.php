<?php

use ass\XmlSecurity\Key as XmlSecurityKey;

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\WsSecurityFilter as BeSimpleWsSecurityFilter;
use BeSimple\SoapCommon\WsSecurityKey as BeSimpleWsSecurityKey;

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation;

use BeSimple\SoapClient\Tests\ServerInterop\TestCase;

class WsSecuritySigEncServerInteropTest extends TestCase
{
    private $options = array(
        'soap_version' => SOAP_1_2,
        'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
        'classmap'        => array(
            'getBook'                => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook',
            'getBookResponse'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse',
            'getBooksByType'         => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType',
            'getBooksByTypeResponse' => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse',
            'addBook'                => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook',
            'addBookResponse'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse',
            'BookInformation'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation',
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
        $this->assertInstanceOf('BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation', $result->getBookReturn);

        $ab = new addBook();
        $ab->isbn = '0445203498';
        $ab->title = 'The Dragon Never Sleeps';
        $ab->author = 'Cook, Glen';
        $ab->type = 'scifi';

        $this->assertTrue((bool) $sc->addBook($ab));

        // getBooksByType("scifi");
    }
}
