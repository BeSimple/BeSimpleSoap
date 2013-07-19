<?php

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\WsSecurityFilter as BeSimpleWsSecurityFilter;

require '../bootstrap.php';

$options = array(
    'soap_version' => SOAP_1_2,
    'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'trace'        => true, // enables use of the methods  SoapClient->__getLastRequest,  SoapClient->__getLastRequestHeaders,  SoapClient->__getLastResponse and  SoapClient->__getLastResponseHeaders
);

/*
 * Deploy "axis_services/library-username-digest.aar" to Apache Axis2 to get
 * this example to work.
 *
 * Using code from axis example:
 * http://www.ibm.com/developerworks/java/library/j-jws4/index.html
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

$sc = new BeSimpleSoapClient('WsSecurityUserPass.wsdl', $options);

$wssFilter = new BeSimpleWsSecurityFilter(true, 600);
$wssFilter->addUserData('libuser', 'books', BeSimpleWsSecurityFilter::PASSWORD_TYPE_TEXT);
//$wssFilter->addUserData( 'libuser', 'books', BeSimpleWsSecurityFilter::PASSWORD_TYPE_DIGEST );

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
