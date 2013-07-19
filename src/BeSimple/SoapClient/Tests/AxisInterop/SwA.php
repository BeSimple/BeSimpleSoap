<?php

use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;
use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;

require '../bootstrap.php';

$options = array(
    'soap_version'    => SOAP_1_1,
    'features'        => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'trace'           => true, // enables use of the methods  SoapClient->__getLastRequest,  SoapClient->__getLastRequestHeaders,  SoapClient->__getLastResponse and  SoapClient->__getLastResponseHeaders
    'attachment_type' => BeSimpleSoapHelper::ATTACHMENTS_TYPE_SWA,
    'cache_wsdl'      => WSDL_CACHE_NONE,
);

/*
 * Deploy "axis_services/besimple-swa.aar" to Apache Axis2 to get this
 * example to work.
 *
 * Run ant to rebuild aar.
 *
 * Example based on:
 * http://axis.apache.org/axis2/java/core/docs/mtom-guide.html#a3
 * http://wso2.org/library/1675
 *
 * Doesn't work directly with ?wsdl served by Apache Axis!
 *
 */

$sc = new BeSimpleSoapClient('SwA.wsdl', $options);

//var_dump($sc->__getFunctions());
//var_dump($sc->__getTypes());

try {
    $file = new stdClass();
    $file->name = 'upload.txt';
    $file->data = 'This is a test text!';
    $result = $sc->uploadFile($file);

    var_dump(
        $result->return
    );

    $file = new stdClass();
    $file->name = 'upload.txt';
    $result = $sc->downloadFile($file);

    var_dump(
        $result->data
    );

    $file = new stdClass();
    $file->name = 'image.jpg'; // source: http://www.freeimageslive.com/galleries/light/pics/swirl3768.jpg
    $file->data = file_get_contents('image.jpg');
    $result = $sc->uploadFile($file);

    var_dump(
        $result->return
    );

    $crc32 = crc32($file->data);

    $file = new stdClass();
    $file->name = 'image.jpg';
    $result = $sc->downloadFile($file);

    file_put_contents('image2.jpg', $result->data);


    var_dump(
        crc32($result->data) === $crc32
    );

} catch (Exception $e) {
    var_dump($e);
}

// var_dump(
//     $sc->__getLastRequestHeaders(),
//     $sc->__getLastRequest(),
//     $sc->__getLastResponseHeaders(),
//     $sc->__getLastResponse()
// );