<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Tests;

use BeSimple\SoapCommon\WsdlHandler;

class WsdlHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsValidMimeTypeType()
    {
        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/WsdlMimeContent.wsdl';
        $wh = new WsdlHandler($filename, SOAP_1_1);

        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'body', 'text/xml'));
        $this->assertFalse($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'body', 'image/gif'));
        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'image/jpeg'));
        $this->assertFalse($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'image/gif'));
        $this->assertFalse($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'text/xml'));

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/WsdlMimeContent2.wsdl';
        $wh = new WsdlHandler($filename, SOAP_1_1);

        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'body', 'text/xml'));
        $this->assertFalse($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'body', 'image/gif'));
        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'image/jpeg'));
        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'image/gif'));
        $this->assertFalse($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'text/xml'));

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/WsdlMimeContent3.wsdl';
        $wh = new WsdlHandler($filename, SOAP_1_1);

        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'body', 'text/xml'));
        $this->assertFalse($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'body', 'image/gif'));
        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'image/jpeg'));
        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'image/gif'));
        $this->assertTrue($wh->isValidMimeTypeType('http://example.com/soapaction', WsdlHandler::BINDING_OPERATION_INPUT, 'ClaimPhoto', 'text/xml'));
    }
}
