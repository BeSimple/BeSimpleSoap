<?php

use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;
use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;

require '../bootstrap.php';

class base64Binary
{
    public $_;
    public $contentType;
}

class AttachmentType
{
    public $fileName;
    public $binaryData;
}

class AttachmentRequest extends AttachmentType
{
}

class base64Binary
{
    public $_;
    public $contentType;
}

class AttachmentType
{
    public $fileName;
    public $binaryData;
}

class AttachmentRequest extends AttachmentType
{
}

class base64Binary
{
    public $_;
    public $contentType;
}

class AttachmentType
{
    public $fileName;
    public $binaryData;
}

class AttachmentRequest extends AttachmentType
{
}

$options = array(
    'soap_version'    => SOAP_1_1,
    'features'        => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'trace'           => true, // enables use of the methods  SoapClient->__getLastRequest,  SoapClient->__getLastRequestHeaders,  SoapClient->__getLastResponse and  SoapClient->__getLastResponseHeaders
    'attachment_type' => BeSimpleSoapHelper::ATTACHMENTS_TYPE_MTOM,
    'cache_wsdl'      => WSDL_CACHE_NONE,
    'classmap'        => array(
        'base64Binary'      => 'base64Binary',
        'AttachmentRequest' => 'AttachmentRequest',
    ),
);

/*
 * Deploy "axis_services/sample-mtom.aar" to Apache Axis2 to get this
 * example to work.
 *
 * Apache Axis2 MTOM example.
 *
 */
$sc = new BeSimpleSoapClient('MTOM.wsdl', $options);

//var_dump($sc->__getFunctions());
//var_dump($sc->__getTypes());

try {
    $b64 = new base64Binary();
    $b64->_ = 'This is a test. :)';
    $b64->contentType = 'text/plain';

    $attachment = new AttachmentRequest();
    $attachment->fileName = 'test123.txt';
    $attachment->binaryData = $b64;

    var_dump($sc->attachment($attachment));

} catch (Exception $e) {
    var_dump($e);
}

// var_dump(
//     $sc->__getLastRequestHeaders(),
//     $sc->__getLastRequest(),
//     $sc->__getLastResponseHeaders(),
//     $sc->__getLastResponse()
// );