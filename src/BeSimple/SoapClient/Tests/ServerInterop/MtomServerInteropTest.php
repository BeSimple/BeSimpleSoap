<?php

use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;
use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\AttachmentRequest;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\AttachmentType;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\base64Binary;

use BeSimple\SoapClient\Tests\ServerInterop\TestCase;

class MtomServerInteropTest extends TestCase
{
    private $options = array(
        'soap_version'    => SOAP_1_1,
        'features'        => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
        'attachment_type' => BeSimpleSoapHelper::ATTACHMENTS_TYPE_MTOM,
        'cache_wsdl'      => WSDL_CACHE_NONE,
        'classmap'        => array(
            'base64Binary'      => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\base64Binary',
            'AttachmentRequest' => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\AttachmentRequest',
        ),
        'proxy_host' => false,
    );

    public function testAttachment()
    {
        $sc = new BeSimpleSoapClient(__DIR__.'/Fixtures/MTOM.wsdl', $this->options);

        $b64 = new base64Binary();
        $b64->_ = 'This is a test. :)';
        $b64->contentType = 'text/plain';

        $attachment = new AttachmentRequest();
        $attachment->fileName = 'test123.txt';
        $attachment->binaryData = $b64;

        $this->assertEquals('File saved succesfully.', $sc->attachment($attachment));

        $fileCreatedByServer = __DIR__.'/'.$attachment->fileName;
        $this->assertEquals($b64->_, file_get_contents($fileCreatedByServer));
        unlink($fileCreatedByServer);
    }
}
