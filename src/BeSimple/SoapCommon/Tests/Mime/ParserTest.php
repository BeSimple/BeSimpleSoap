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

use BeSimple\SoapCommon\Mime\MultiPart;
use BeSimple\SoapCommon\Mime\Parser;
use BeSimple\SoapCommon\Mime\Part;
use BeSimple\SoapCommon\Mime\PartHeader;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParserRequestWsi()
    {
        $filename = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/WS-I-MTOM-request.txt';
        $mimeMessage = file_get_contents($filename);

        $mp = Parser::parseMimeMessage($mimeMessage);
        $this->assertsForWsiMtomRequest($mp);
    }

    public function testParserResponseAmazon()
    {
        $filename = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/SwA-response-amazon.txt';
        $mimeMessage = file_get_contents($filename);

        $mp = Parser::parseMimeMessage($mimeMessage);
        $this->assertEquals('Fri, 12 Feb 2010 15:46:00 GMT', $mp->getHeader('Date'));
        $this->assertEquals('Server', $mp->getHeader('Server'));
        $this->assertEquals('1.0', $mp->getHeader('MIME-Version'));
        $this->assertEquals('close', $mp->getHeader('Cneonction'));
        $this->assertEquals('chunked', $mp->getHeader('Transfer-Encoding'));
        $this->assertEquals('multipart/related', $mp->getHeader('Content-Type'));
        $this->assertEquals('text/xml', $mp->getHeader('Content-Type', 'type'));
        $this->assertEquals('utf-8', $mp->getHeader('Content-Type', 'charset'));
        $this->assertEquals('xxx-MIME-Boundary-xxx-0xa36cb38-0a36cb38-xxx-END-xxx', $mp->getHeader('Content-Type', 'boundary'));

        $p1 = $mp->getPart();
        $this->assertInstanceOf('BeSimple\SoapCommon\Mime\Part', $p1);
        $this->assertEquals('text/xml', $p1->getHeader('Content-Type'));
        $this->assertEquals('UTF-8', $p1->getHeader('Content-Type', 'charset'));
        $this->assertEquals(389, strlen($p1->getContent()));

        $p2 = $mp->getPart('0x9d6ad00-0xa19ef48-0x9de7500-0xa4fae78-0xa382698');
        $this->assertInstanceOf('BeSimple\SoapCommon\Mime\Part', $p1);
        $this->assertEquals('binary', $p2->getHeader('Content-Transfer-Encoding'));
        $this->assertEquals('application/binary', $p2->getHeader('Content-Type'));
        $this->assertEquals(79, strlen($p2->getContent()));
    }

    public function testParserResponseAxis()
    {
        $filename = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/SwA-response-axis.txt';
        $mimeMessage = file_get_contents($filename);

        $mp = Parser::parseMimeMessage($mimeMessage);
        $this->assertEquals('Sat, 11 Sep 2010 12:52:57 GMT', $mp->getHeader('Date'));
        $this->assertEquals('Simple-Server/1.1', $mp->getHeader('Server'));
        $this->assertEquals('1.0', $mp->getHeader('MIME-Version'));
        $this->assertEquals('chunked', $mp->getHeader('Transfer-Encoding'));
        $this->assertEquals('multipart/related', $mp->getHeader('Content-Type'));
        $this->assertEquals('application/soap+xml', $mp->getHeader('Content-Type', 'type'));
        $this->assertEquals('utf-8', $mp->getHeader('Content-Type', 'charset'));
        $this->assertEquals('<0.urn:uuid:2DB7ABF3DC5BED7FA51284209577583@apache.org>', $mp->getHeader('Content-Type', 'start'));
        $this->assertEquals('urn:getVersionResponse', $mp->getHeader('Content-Type', 'action'));
        $this->assertEquals('MIMEBoundaryurn_uuid_2DB7ABF3DC5BED7FA51284209577582', $mp->getHeader('Content-Type', 'boundary'));

        $p1 = $mp->getPart('0.urn:uuid:2DB7ABF3DC5BED7FA51284209577583@apache.org');
        $this->assertInstanceOf('BeSimple\SoapCommon\Mime\Part', $p1);
        $this->assertEquals('8bit', $p1->getHeader('Content-Transfer-Encoding'));
        $this->assertEquals('application/soap+xml', $p1->getHeader('Content-Type'));
        $this->assertEquals('utf-8', $p1->getHeader('Content-Type', 'charset'));
        $this->assertEquals(499, strlen($p1->getContent()));
    }

    public function testParserResponseWsi()
    {
        $filename = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/WS-I-MTOM-response.txt';
        $mimeMessage = file_get_contents($filename);

        $mp = Parser::parseMimeMessage($mimeMessage);
        $this->assertEquals('Keep-Alive', $mp->getHeader('Proxy-Connection'));
        $this->assertEquals('Keep-Alive', $mp->getHeader('Connection'));
        $this->assertEquals('1166', $mp->getHeader('Content-Length'));
        $this->assertEquals('1.1 RED-PRXY-03', $mp->getHeader('Via'));
        $this->assertEquals('Fri, 09 Sep 2005 06:57:22 GMT', $mp->getHeader('Date'));
        $this->assertEquals('multipart/related', $mp->getHeader('Content-Type'));
        $this->assertEquals('application/xop+xml', $mp->getHeader('Content-Type', 'type'));
        $this->assertEquals('utf-8', $mp->getHeader('Content-Type', 'charset'));
        $this->assertEquals('<http://tempuri.org/0>', $mp->getHeader('Content-Type', 'start'));
        $this->assertEquals('application/soap+xml', $mp->getHeader('Content-Type', 'start-info'));
        $this->assertEquals('uuid:b71dc628-ec8f-4422-8a4a-992f041cb94c+id=46', $mp->getHeader('Content-Type', 'boundary'));

        $p1 = $mp->getPart('http://tempuri.org/0');
        $this->assertInstanceOf('BeSimple\SoapCommon\Mime\Part', $p1);
        $this->assertEquals('8bit', $p1->getHeader('Content-Transfer-Encoding'));
        $this->assertEquals('application/xop+xml', $p1->getHeader('Content-Type'));
        $this->assertEquals('utf-8', $p1->getHeader('Content-Type', 'charset'));
        $this->assertEquals('application/soap+xml', $p1->getHeader('Content-Type', 'type'));
        $this->assertEquals(910, strlen($p1->getContent()));
    }

    public function testParserWithHeaderArray()
    {
        $filename = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/WS-I-MTOM-request_noheader.txt';
        $mimeMessage = file_get_contents($filename);

        $headers = array(
            'Content-Type' => 'multipart/related; type="application/xop+xml";start="<http://tempuri.org/0>";boundary="uuid:0ca0e16e-feb1-426c-97d8-c4508ada5e82+id=7";start-info="application/soap+xml"',
            'Content-Length' => 1941,
            'Host' => '131.107.72.15',
            'Expect' => '100-continue',
        );

        $mp = Parser::parseMimeMessage($mimeMessage, $headers);
        $this->assertsForWsiMtomRequest($mp);
    }

    /*
     * used in:
     * - testParserWithHeaderArray
     * - testParserRequestWsi
     */
    private function assertsForWsiMtomRequest(MultiPart $mp)
    {
        $this->assertEquals('multipart/related', $mp->getHeader('Content-Type'));
        $this->assertEquals('application/xop+xml', $mp->getHeader('Content-Type', 'type'));
        $this->assertEquals('utf-8', $mp->getHeader('Content-Type', 'charset'));
        $this->assertEquals('<http://tempuri.org/0>', $mp->getHeader('Content-Type', 'start'));
        $this->assertEquals('application/soap+xml', $mp->getHeader('Content-Type', 'start-info'));
        $this->assertEquals('uuid:0ca0e16e-feb1-426c-97d8-c4508ada5e82+id=7', $mp->getHeader('Content-Type', 'boundary'));
        $this->assertEquals('1941', $mp->getHeader('Content-Length'));
        $this->assertEquals('131.107.72.15', $mp->getHeader('Host'));
        $this->assertEquals('100-continue', $mp->getHeader('Expect'));

        $p1 = $mp->getPart('http://tempuri.org/0');
        $this->assertInstanceOf('BeSimple\SoapCommon\Mime\Part', $p1);
        $this->assertEquals('8bit', $p1->getHeader('Content-Transfer-Encoding'));
        $this->assertEquals('application/xop+xml', $p1->getHeader('Content-Type'));
        $this->assertEquals('utf-8', $p1->getHeader('Content-Type', 'charset'));
        $this->assertEquals('application/soap+xml', $p1->getHeader('Content-Type', 'type'));
        $this->assertEquals(737, strlen($p1->getContent()));

        $p2 = $mp->getPart('http://tempuri.org/1/632618206527087310');
        $this->assertInstanceOf('BeSimple\SoapCommon\Mime\Part', $p1);
        $this->assertEquals('binary', $p2->getHeader('Content-Transfer-Encoding'));
        $this->assertEquals('application/octet-stream', $p2->getHeader('Content-Type'));
        $this->assertEquals(769, strlen($p2->getContent()));
    }
}
