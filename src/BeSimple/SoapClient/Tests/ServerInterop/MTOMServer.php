<?php

use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;
use BeSimple\SoapServer\SoapServer as BeSimpleSoapServer;

require '../../../BeSimpleSoapServer/tests/bootstrap.php';

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
    'attachment_type' => BeSimpleSoapHelper::ATTACHMENTS_TYPE_MTOM,
    'cache_wsdl'      => WSDL_CACHE_NONE,
    'classmap'        => array(
        'base64Binary'      => 'base64Binary',
        'AttachmentRequest' => 'AttachmentRequest',
    ),
);

class Mtom
{
    public function attachment(AttachmentRequest $attachment)
    {
        $b64 = $attachment->binaryData;

        file_put_contents('test.txt', var_export(array(
            $attachment->fileName,
            $b64->_,
            $b64->contentType
        ), true));

        return 'done';
    }
}

$ss = new BeSimpleSoapServer('MTOM.wsdl', $options);
$ss->setClass('Mtom');
$ss->handle();
