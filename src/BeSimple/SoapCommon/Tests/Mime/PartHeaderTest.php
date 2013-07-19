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

use BeSimple\SoapCommon\Mime\PartHeader;
use BeSimple\SoapCommon\Tests\Fixtures\MimePartHeader;

class PartHeaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetHeader()
    {
        $ph = new MimePartHeader();
        $ph->setHeader('Content-Type', 'text/xml');
        $this->assertEquals('text/xml', $ph->getHeader('Content-Type'));
    }

    public function testSetGetHeaderSubvalue()
    {
        $ph = new MimePartHeader();
        $ph->setHeader('Content-Type', 'utf-8', 'charset');
        $this->assertEquals(null, $ph->getHeader('Content-Type', 'charset'));

        $ph->setHeader('Content-Type', 'text/xml');
        $ph->setHeader('Content-Type', 'charset', 'utf-8');
        $this->assertEquals('utf-8', $ph->getHeader('Content-Type', 'charset'));
    }

    public function testGenerateHeaders()
    {
        $ph = new MimePartHeader();

        $class = new \ReflectionClass($ph);
        $method = $class->getMethod('generateHeaders');
        $method->setAccessible(true);

        $this->assertEquals('', $method->invoke($ph));

        $ph->setHeader('Content-Type', 'text/xml');
        $this->assertEquals("Content-Type: text/xml\r\n", $method->invoke($ph));

        $ph->setHeader('Content-Type', 'charset', 'utf-8');
        $this->assertEquals("Content-Type: text/xml; charset=utf-8\r\n", $method->invoke($ph));

        $ph->setHeader('Content-Type', 'type', 'text/xml');
        $this->assertEquals("Content-Type: text/xml; charset=utf-8; type=\"text/xml\"\r\n", $method->invoke($ph));
    }
}
