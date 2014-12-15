<?php

error_reporting(0);

require '../../../../../vendor/autoload.php';

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

$options = array(
    'soap_version' => SOAP_1_2,
    'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'trace'           => true, // enables use of the methods  SoapClient->__getLastRequest,  SoapClient->__getLastRequestHeaders,  SoapClient->__getLastResponse and  SoapClient->__getLastResponseHeaders
    'classmap'        => array(
        'getBook'                => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook',
        'getBookResponse'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse',
        'getBooksByType'         => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType',
        'getBooksByTypeResponse' => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse',
        'addBook'                => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook',
        'addBookResponse'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse',
        'BookInformation'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation',
    ),
);

$sc = new BeSimpleSoapClient(__DIR__.'/Fixtures/WsSecuritySigEnc.wsdl', $options);

//var_dump($sc->__getFunctions());
//var_dump($sc->__getTypes());

try {
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
    var_dump($result->getBookReturn);

    $ab = new addBook();
    $ab->isbn = '0445203498';
    $ab->title = 'The Dragon Never Sleeps';
    $ab->author = 'Cook, Glen';
    $ab->type = 'scifi';

    var_dump($sc->addBook($ab));

} catch (Exception $e) {
    var_dump($e);
}

// var_dump(
//     $sc->__getLastRequestHeaders(),
//     $sc->__getLastRequest(),
//     $sc->__getLastResponseHeaders(),
//     $sc->__getLastResponse()
// );
