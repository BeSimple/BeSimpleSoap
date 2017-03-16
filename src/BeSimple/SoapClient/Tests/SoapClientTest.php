<?php

namespace BeSimple\SoapClient\Tests;

use BeSimple\SoapClient\SoapClient;

class SoapClientTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLastResponseCode() {

        $stub = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['__getLastResponseHeaders'])
            ->getMock();
        $stub->method('__getLastResponseHeaders')
            ->willReturn("HTTP/1.1 200 OK\nContent-Type: text/html");
        $lastResponseCode = $stub->__getLastResponseCode();
        $this->assertEquals(200, $lastResponseCode);
    }
}