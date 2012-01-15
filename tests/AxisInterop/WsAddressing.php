<?php

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\WsAddressingFilter as BeSimpleWsAddressingFilter;

require '../bootstrap.php';

$options = array(
    'soap_version' => SOAP_1_2,
    'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'trace'        => true, // enables use of the methods  SoapClient->__getLastRequest,  SoapClient->__getLastRequestHeaders,  SoapClient->__getLastResponse and  SoapClient->__getLastResponseHeaders
);

/*
 * Deploy "axis_services/version2.aar" to Apache Axis2 to get this example to
 * work.
 *
 * To rebuild the "axis_services/version2.aar" the following steps need to be
 * done to build a working Apache Axis2 version service with SOAP session
 * enabled.
 *
 * 1) Go to $AXIS_HOME/samples/version and edit the following files:
 *
 * resources/META-INF/services.xml:
 * <service name="Version2" scope="soapsession">
 * ...
 * </service>
 *
 * build.xml:
 * replace version.aar with version2.aar
 *
 * 2) Run ant build.xml in "$AXIS_HOME/samples/version"
 *
 */

$sc = new BeSimpleSoapClient('http://localhost:8080/axis2/services/Version2?wsdl', $options);
$soapKernel = $sc->getSoapKernel();
$wsaFilter = new BeSimpleWsAddressingFilter();
$soapKernel->registerFilter($wsaFilter);

//var_dump($sc->__getFunctions());
//var_dump($sc->__getTypes());

try {
    $wsaFilter->setReplyTo(BeSimpleWsAddressingFilter::ENDPOINT_REFERENCE_ANONYMOUS);
    $wsaFilter->setMessageId();

    var_dump($sc->getVersion());

    $soapSessionId1 = $wsaFilter->getReferenceParameter('http://ws.apache.org/namespaces/axis2', 'ServiceGroupId');
    echo 'ID1: ' .$soapSessionId1 . PHP_EOL;

    $wsaFilter->addReferenceParameter('http://ws.apache.org/namespaces/axis2', 'axis2', 'ServiceGroupId', $soapSessionId1);

    var_dump($sc->getVersion());

    $soapSessionId2 = $wsaFilter->getReferenceParameter('http://ws.apache.org/namespaces/axis2', 'ServiceGroupId');
    echo 'ID2: ' . $soapSessionId2 . PHP_EOL;

    if ($soapSessionId1 == $soapSessionId2) {
        echo PHP_EOL;
        echo 'SOAP session worked :)';
    }
} catch (Exception $e) {
    var_dump($e);
}

// var_dump(
//     $sc->__getLastRequestHeaders(),
//     $sc->__getLastRequest(),
//     $sc->__getLastResponseHeaders(),
//     $sc->__getLastResponse()
// );