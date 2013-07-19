<?php

use ass\XmlSecurity\Key as XmlSecurityKey;

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\WsSecurityFilter as BeSimpleWsSecurityFilter;
use BeSimple\SoapCommon\WsSecurityKey as BeSimpleWsSecurityKey;

require '../bootstrap.php';

echo '<pre>';

$options = array(
    'soap_version' => SOAP_1_2,
    'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'trace'        => true, // enables use of the methods  SoapClient->__getLastRequest,  SoapClient->__getLastRequestHeaders,  SoapClient->__getLastResponse and  SoapClient->__getLastResponseHeaders
);

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

class getBook {}
class getBookResponse {}
class getBooksByType {}
class getBooksByTypeResponse {}
class addBook {}
class addBookResponse {}
class BookInformation {}

$options['classmap'] = array(
    'getBook' => 'getBook',
    'getBookResponse' => 'getBookResponse',
    'getBooksByType' => 'getBooksByType',
    'getBooksByTypeResponse' => 'getBooksByTypeResponse',
    'addBook' => 'addBook',
    'addBookResponse' => 'addBookResponse',
    'BookInformation' => 'BookInformation',
);

$sc = new BeSimpleSoapClient('WsSecuritySigEnc.wsdl', $options);

$wssFilter = new BeSimpleWsSecurityFilter();
// user key for signature and encryption
$securityKeyUser = new BeSimpleWsSecurityKey();
$securityKeyUser->addPrivateKey(XmlSecurityKey::RSA_SHA1, 'clientkey.pem', true);
$securityKeyUser->addPublicKey(XmlSecurityKey::RSA_SHA1, 'clientcert.pem', true);
$wssFilter->setUserSecurityKeyObject($securityKeyUser);
// service key for encryption
$securityKeyService = new BeSimpleWsSecurityKey();
$securityKeyService->addPrivateKey(XmlSecurityKey::TRIPLEDES_CBC);
$securityKeyService->addPublicKey(XmlSecurityKey::RSA_1_5, 'servercert.pem', true);
$wssFilter->setServiceSecurityKeyObject($securityKeyService);
// TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER | TOKEN_REFERENCE_SECURITY_TOKEN | TOKEN_REFERENCE_THUMBPRINT_SHA1
$wssFilter->setSecurityOptionsSignature(BeSimpleWsSecurityFilter::TOKEN_REFERENCE_SECURITY_TOKEN);
$wssFilter->setSecurityOptionsEncryption(BeSimpleWsSecurityFilter::TOKEN_REFERENCE_THUMBPRINT_SHA1);

$soapKernel = $sc->getSoapKernel();
$soapKernel->registerFilter($wssFilter);

//var_dump($sc->__getFunctions());
//var_dump($sc->__getTypes());

try {
    $gb = new getBook();
    $gb->isbn = '0061020052';
    var_dump($sc->getBook($gb));

    $ab = new addBook();
    $ab->isbn = '0445203498';
    $ab->title = 'The Dragon Never Sleeps';
    $ab->author = 'Cook, Glen';
    $ab->type = 'scifi';
    var_dump($sc->addBook($ab));

    // getBooksByType("scifi");
} catch (Exception $e) {
    var_dump($e);
}

//var_dump(
//    $sc->__getLastRequestHeaders(),
//    $sc->__getLastRequest(),
//    $sc->__getLastResponseHeaders(),
//    $sc->__getLastResponse()
//);
