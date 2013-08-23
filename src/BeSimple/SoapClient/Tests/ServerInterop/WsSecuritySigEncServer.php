<?php

require '../../../../../vendor/autoload.php';

use ass\XmlSecurity\Key as XmlSecurityKey;

use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;
use BeSimple\SoapServer\SoapServer as BeSimpleSoapServer;
use BeSimple\SoapServer\WsSecurityFilter as BeSimpleWsSecurityFilter;
use BeSimple\SoapCommon\WsSecurityKey as BeSimpleWsSecurityKey;

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation;

$options = array(
    'soap_version'    => SOAP_1_1,
    'features'        => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'cache_wsdl'      => WSDL_CACHE_NONE,
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

class WsSecuritySigEncServer
{
    public function getBook(getBook $gb)
    {
        $bi = new BookInformation();
        $bi->isbn = $gb->isbn;
        $bi->title = 'title';
        $bi->author = 'author';
        $bi->type = 'scifi';

        $br = new getBookResponse();
        $br->getBookReturn = $bi;

        return $br;
    }

    public function addBook(addBook $ab)
    {
        $abr = new addBookResponse();
        $abr->addBookReturn = true;

        return $abr;
    }
}

$ss = new BeSimpleSoapServer(__DIR__.'/Fixtures/WsSecurityUserPass.wsdl', $options);

$wssFilter = new BeSimpleWsSecurityFilter();

// user key for signature and encryption
$securityKeyUser = new BeSimpleWsSecurityKey();
$securityKeyUser->addPrivateKey(XmlSecurityKey::RSA_SHA1, __DIR__.'/Fixtures/serverkey.pem', true);
$securityKeyUser->addPublicKey(XmlSecurityKey::RSA_SHA1, __DIR__.'/Fixtures/servercert.pem', true);
$wssFilter->setUserSecurityKeyObject($securityKeyUser);
// service key for encryption
$securityKeyService = new BeSimpleWsSecurityKey();
$securityKeyService->addPrivateKey(XmlSecurityKey::TRIPLEDES_CBC);
$securityKeyService->addPublicKey(XmlSecurityKey::RSA_1_5, __DIR__.'/Fixtures/clientcert.pem', true);
$wssFilter->setServiceSecurityKeyObject($securityKeyService);
// TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER | TOKEN_REFERENCE_SECURITY_TOKEN | TOKEN_REFERENCE_THUMBPRINT_SHA1
$wssFilter->setSecurityOptionsSignature(BeSimpleWsSecurityFilter::TOKEN_REFERENCE_SECURITY_TOKEN);
$wssFilter->setSecurityOptionsEncryption(BeSimpleWsSecurityFilter::TOKEN_REFERENCE_THUMBPRINT_SHA1);

$soapKernel = $ss->getSoapKernel();
$soapKernel->registerFilter($wssFilter);

$ss->setClass('WsSecuritySigEncServer');
$ss->handle();
