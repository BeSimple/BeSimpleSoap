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

namespace BeSimple\SoapClient;

use BeSimple\SoapClient\Curl;

/**
* @author Andreas Schamberger
*/
class CurlTest extends \PHPUnit_Framework_TestCase
{
    protected $webserverProcessId;

    protected function startPhpWebserver()
    {
        if ('Windows' == substr(php_uname('s'), 0, 7 )) {
            $powershellCommand = "\$app = start-process php.exe -ArgumentList '-S localhost:8000 -t ".__DIR__.DIRECTORY_SEPARATOR."Fixtures' -WindowStyle 'Hidden' -passthru; Echo \$app.Id;";
            $shellCommand = 'powershell -command "& {'.$powershellCommand.'}"';
        } else {
            $shellCommand = "nohup php -S localhost:8000 -t ".__DIR__.DIRECTORY_SEPARATOR."Fixtures &";
        }
        $output = array();
        exec($shellCommand, $output);
        $this->webserverProcessId = $output[0]; // pid is in first element
    }

    protected function stopPhpWebserver()
    {
        if (!is_null($this->webserverProcessId)) {
            if ('Windows' == substr(php_uname('s'), 0, 7 )) {
                exec('TASKKILL /F /PID ' . $this->webserverProcessId);
            } else {
                exec('kill ' . $this->webserverProcessId);
            }
            $this->webserverProcessId = null;
        }
    }

    public function testExec()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $this->assertTrue($curl->exec('http://localhost:8000/curl.txt'));
        $this->assertTrue($curl->exec('http://localhost:8000/404.txt'));

        $this->stopPhpWebserver();
    }

    public function testGetErrorMessage()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $curl->exec('http://unknown/curl.txt');
        $this->assertEquals('Could not connect to host', $curl->getErrorMessage());

        $curl->exec('xyz://localhost:8000/@404.txt');
        $this->assertEquals('Unknown protocol. Only http and https are allowed.', $curl->getErrorMessage());

        $curl->exec('');
        $this->assertEquals('Unable to parse URL', $curl->getErrorMessage());

        $this->stopPhpWebserver();
    }

    public function testGetRequestHeaders()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $curl->exec('http://localhost:8000/curl.txt');
        $this->assertEquals(136, strlen($curl->getRequestHeaders()));

        $curl->exec('http://localhost:8000/404.txt');
        $this->assertEquals(135, strlen($curl->getRequestHeaders()));

        $this->stopPhpWebserver();
    }

    public function testGetResponse()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $curl->exec('http://localhost:8000/curl.txt');
        $this->assertEquals(150, strlen($curl->getResponse()));

        $curl->exec('http://localhost:8000/404.txt');
        $this->assertEquals(1282, strlen($curl->getResponse()));

        $this->stopPhpWebserver();
    }

    public function testGetResponseBody()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $curl->exec('http://localhost:8000/curl.txt');
        $this->assertEquals('This is a testfile for cURL.', $curl->getResponseBody());

        $this->stopPhpWebserver();
    }

    public function testGetResponseContentType()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $curl->exec('http://localhost:8000/curl.txt');
        $this->assertEquals('text/plain; charset=UTF-8', $curl->getResponseContentType());

        $curl->exec('http://localhost:8000/404.txt');
        $this->assertEquals('text/html; charset=UTF-8', $curl->getResponseContentType());

        $this->stopPhpWebserver();
    }

    public function testGetResponseHeaders()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $curl->exec('http://localhost:8000/curl.txt');
        $this->assertEquals(122, strlen($curl->getResponseHeaders()));

        $curl->exec('http://localhost:8000/404.txt');
        $this->assertEquals(130, strlen($curl->getResponseHeaders()));

        $this->stopPhpWebserver();
    }

    public function testGetResponseStatusCode()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $curl->exec('http://localhost:8000/curl.txt');
        $this->assertEquals(200, $curl->getResponseStatusCode());

        $curl->exec('http://localhost:8000/404.txt');
        $this->assertEquals(404, $curl->getResponseStatusCode());

        $this->stopPhpWebserver();
    }

    public function testGetResponseStatusMessage()
    {
        $this->startPhpWebserver();

        $curl = new Curl();

        $curl->exec('http://localhost:8000/curl.txt');
        $this->assertEquals('OK', $curl->getResponseStatusMessage());

        $curl->exec('http://localhost:8000/404.txt');
        $this->assertEquals('Not Found', $curl->getResponseStatusMessage());

        $this->stopPhpWebserver();
    }
}