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

namespace BeSimple\Tests\SoapClient;

use BeSimple\SoapClient\SoapRequest;

class SoapRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testSetFunction()
    {
        $soapRequest = new SoapRequest();
        $soapRequest->setFunction('foo');

        $this->assertEquals('foo', $soapRequest->getFunction());
    }

    public function testSetArguments()
    {
        $soapRequest = new SoapRequest();
        $arguments   = array(
            'foo' => true,
            'bar' => false,
        );
        $soapRequest->setArguments($arguments);

        $this->assertEquals($arguments, $soapRequest->getArguments());
    }

    public function testGetArgument()
    {
        $soapRequest = new SoapRequest();

        $this->assertSame(null, $soapRequest->getArgument('foo'));
        $this->assertFalse($soapRequest->getArgument('foo', false));

        $soapRequest->addArgument('foo', 'bar');

        $this->assertEquals('bar', $soapRequest->getArgument('foo', false));
    }

    public function testSetOptions()
    {
        $soapRequest = new SoapRequest();
        $options     = array(
            'uri'        => 'foo',
            'soapaction' => 'bar',
        );
        $soapRequest->setOptions($options);

        $this->assertEquals($options, $soapRequest->getOptions());
    }

    public function testGetOption()
    {
        $soapRequest = new SoapRequest();

        $this->assertSame(null, $soapRequest->getOption('soapaction'));
        $this->assertFalse($soapRequest->getOption('soapaction', false));

        $soapRequest->addOption('soapaction', 'foo');

        $this->assertEquals('foo', $soapRequest->getOption('soapaction'));
    }

    public function testSetHeaders()
    {
        $soapRequest = new SoapRequest();

        $this->assertEquals(array(), $soapRequest->getHeaders());

        $header1 = new \SoapHeader('foobar', 'foo', 'bar');
        $header2 = new \SoapHeader('barfoo', 'bar', 'foo');
        $soapRequest
            ->addHeader($header1)
            ->addHeader($header2)
        ;

        $this->assertSame(array($header1, $header2), $soapRequest->getHeaders());
    }

    public function testConstruct()
    {
        $soapRequest = new SoapRequest();

        $this->assertNull($soapRequest->getFunction());
        $this->assertEquals(array(), $soapRequest->getArguments());
        $this->assertEquals(array(), $soapRequest->getOptions());
        $this->assertEquals(array(), $soapRequest->getHeaders());

        $arguments   = array('bar' => 'foobar');
        $options     = array('soapaction' => 'foobar');
        $headers     = array(
            new \SoapHeader('foobar', 'foo', 'bar'),
            new \SoapHeader('barfoo', 'bar', 'foo'),
        );
        $soapRequest = new SoapRequest('foo', $arguments, $options, $headers);

        $this->assertEquals('foo', $soapRequest->getFunction());
        $this->assertEquals($arguments, $soapRequest->getArguments());
        $this->assertEquals($options, $soapRequest->getOptions());
        $this->assertSame($headers, $soapRequest->getHeaders());
    }
}