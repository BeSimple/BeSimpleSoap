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
use BeSimple\SoapCommon\Mime\Part;
use BeSimple\SoapCommon\Mime\PartHeader;

class MultiPartTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $mp = new MultiPart();

        $this->assertEquals('1.0', $mp->getHeader('MIME-Version'));
        $this->assertEquals('multipart/related', $mp->getHeader('Content-Type'));
        $this->assertEquals('text/xml', $mp->getHeader('Content-Type', 'type'));
        $this->assertEquals('utf-8', $mp->getHeader('Content-Type', 'charset'));
        $this->assertRegExp('~urn:uuid:.*~', $mp->getHeader('Content-Type', 'boundary'));
    }

    public function testGetMimeMessage()
    {
        $mp = new MultiPart();

        /*
        string(51) "
        --urn:uuid:a81ca327-591e-4656-91a1-8f177ada95b0--"
        */
        $this->assertEquals(51, strlen($mp->getMimeMessage()));

        $p = new Part('test');
        $mp->addPart($p, true);

        /*
        string(259) "
        --urn:uuid:a81ca327-591e-4656-91a1-8f177ada95b0
        Content-Type: application/octet-stream; charset=utf-8
        Content-Transfer-Encoding: binary
        Content-ID: <urn:uuid:a0ad4376-5b08-4471-9f6f-c29aee881e84>

        test
        --urn:uuid:a81ca327-591e-4656-91a1-8f177ada95b0--"
        */
        $this->assertEquals(259, strlen($mp->getMimeMessage()));
    }

    public function testGetMimeMessageWithHeaders()
    {
        $mp = new MultiPart();

        /*
        string(189) "MIME-Version: 1.0
        Content-Type: multipart/related; type="text/xml"; charset=utf-8; boundary="urn:uuid:231833e2-a23b-410a-862e-250524fc38f6"

        --urn:uuid:231833e2-a23b-410a-862e-250524fc38f6--"
        */
        $this->assertEquals(193, strlen($mp->getMimeMessage(true)));

        $p = new Part('test');
        $mp->addPart($p, true);

        /*
        string(452) "MIME-Version: 1.0
        Content-Type: multipart/related; type="text/xml"; charset=utf-8; boundary="urn:uuid:231833e2-a23b-410a-862e-250524fc38f6"; start="<urn:uuid:9389c081-56f7-4f57-b66e-c81892c3d4db>"

        --urn:uuid:231833e2-a23b-410a-862e-250524fc38f6
        Content-Type: application/octet-stream; charset=utf-8
        Content-Transfer-Encoding: binary
        Content-ID: <urn:uuid:9389c081-56f7-4f57-b66e-c81892c3d4db>

        test
        --urn:uuid:231833e2-a23b-410a-862e-250524fc38f6--"
        */
        $this->assertEquals(458, strlen($mp->getMimeMessage(true)));
    }

    public function testGetHeadersForHttp()
    {
        $mp = new MultiPart();

        $result = array(
            'Content-Type: multipart/related; type="text/xml"; charset=utf-8; boundary="' . $mp->getHeader('Content-Type', 'boundary') . '"',
        );
        $this->assertEquals($result, $mp->getHeadersForHttp());

        $result = array(
            'Content-Type: multipart/related; type="text/xml"; charset=utf-8; boundary="' . $mp->getHeader('Content-Type', 'boundary') . '"',
            'Content-Description: test',
        );
        $mp->setHeader('Content-Description', 'test');
        $this->assertEquals($result, $mp->getHeadersForHttp());
    }

    public function testAddGetPart()
    {
        $mp = new MultiPart();

        $p = new Part('test');
        $p->setHeader('Content-ID', 'mycontentid');
        $mp->addPart($p);
        $this->assertEquals($p, $mp->getPart('mycontentid'));
    }

    public function testAddGetPartWithMain()
    {
        $mp = new MultiPart();

        $p = new Part('test');
        $mp->addPart($p, true);
        $this->assertEquals($p, $mp->getPart());
    }

    public function testGetParts()
    {
        $mp = new MultiPart();

        $p1 = new Part('test');
        $mp->addPart($p1, true);
        $p2 = new Part('test');
        $mp->addPart($p2);

        $withoutMain = array(
            trim($p2->getHeader('Content-ID'),'<>') => $p2,
        );
        $this->assertEquals($withoutMain, $mp->getParts());

        $withMain = array(
            trim($p1->getHeader('Content-ID'),'<>') => $p1,
            trim($p2->getHeader('Content-ID'),'<>') => $p2,
        );
        $this->assertEquals($withMain, $mp->getParts(true));
    }
}
