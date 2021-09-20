<?php

/*
 * This file is part of the BeSimpleSoapClient.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapClient\Tests;

use BeSimple\SoapClient\Curl;

/**
 * @author Andreas Schamberger <mail@andreass.net>
 */
class CurlTest extends AbstractWebserverTest
{
    public function testExec()
    {
        $curl = new Curl(array(
            'proxy_host' => false,
        ));

        $this->assertTrue($curl->exec(sprintf('http://localhost:%d/curl.txt', WEBSERVER_PORT)));
        $this->assertTrue($curl->exec(sprintf('http://localhost:%d/404.txt', WEBSERVER_PORT)));
    }

    public function testGetErrorMessage()
    {
        $curl = new Curl(array(
            'proxy_host' => false,
        ));

        $curl->exec('http://unknown/curl.txt');
        $this->assertEquals('Could not connect to host', $curl->getErrorMessage());

        $curl->exec(sprintf('xyz://localhost:%d/@404.txt', WEBSERVER_PORT));
        $this->assertEquals('Unknown protocol. Only http and https are allowed.', $curl->getErrorMessage());

        $curl->exec('');
        $this->assertEquals('Unable to parse URL', $curl->getErrorMessage());
    }

    public function testGetRequestHeaders()
    {
        $curl = new Curl(array(
            'proxy_host' => false,
        ));

        $curl->exec(sprintf('http://localhost:%d/curl.txt', WEBSERVER_PORT));
        $this->assertEquals(142 + self::$websererPortLength, strlen($curl->getRequestHeaders()));

        $curl->exec(sprintf('http://localhost:%s/404.txt', WEBSERVER_PORT));
        $this->assertEquals(141 + self::$websererPortLength, strlen($curl->getRequestHeaders()));
    }

    public function testGetResponse()
    {
        $curl = new Curl(array(
            'proxy_host' => false,
        ));

        $curl->exec(sprintf('http://localhost:%d/curl.txt', WEBSERVER_PORT));
        $this->assertSame('OK', $curl->getResponseStatusMessage());

        $this->assertEquals(182 + self::$websererPortLength, strlen($curl->getResponse()));

        $curl->exec(sprintf('http://localhost:%d/404.txt', WEBSERVER_PORT));
        $this->assertSame('Not Found', $curl->getResponseStatusMessage());
    }

    public function testGetResponseBody()
    {
        $curl = new Curl(array(
            'proxy_host' => false,
        ));

        $curl->exec(sprintf('http://localhost:%d/curl.txt', WEBSERVER_PORT));
        $this->assertEquals('This is a testfile for cURL.', $curl->getResponseBody());
    }

    public function testGetResponseContentType()
    {
        $curl = new Curl(array(
            'proxy_host' => false,
        ));

        $curl->exec(sprintf('http://localhost:%d/curl.txt', WEBSERVER_PORT));
        $this->assertEquals('text/plain; charset=UTF-8', $curl->getResponseContentType());

        $curl->exec(sprintf('http://localhost:%d/404.txt', WEBSERVER_PORT));
        $this->assertEquals('text/html; charset=UTF-8', $curl->getResponseContentType());
    }

    public function testGetResponseHeaders()
    {
        $curl = new Curl(array(
            'proxy_host' => false,
        ));

        $curl->exec(sprintf('http://localhost:%d/curl.txt', WEBSERVER_PORT));
        $this->assertEquals(154 + self::$websererPortLength, strlen($curl->getResponseHeaders()));

        $curl->exec(sprintf('http://localhost:%d/404.txt', WEBSERVER_PORT));
        $this->assertEquals(161 + self::$websererPortLength, strlen($curl->getResponseHeaders()));
    }

    public function testGetResponseStatusCode()
    {
        $curl = new Curl(array(
            'proxy_host' => false,
        ));

        $curl->exec(sprintf('http://localhost:%d/curl.txt', WEBSERVER_PORT));
        $this->assertEquals(200, $curl->getResponseStatusCode());

        $curl->exec(sprintf('http://localhost:%d/404.txt', WEBSERVER_PORT));
        $this->assertEquals(404, $curl->getResponseStatusCode());
    }
}
