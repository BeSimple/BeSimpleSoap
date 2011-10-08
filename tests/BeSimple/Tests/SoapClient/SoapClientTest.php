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

use BeSimple\SoapCommon\Cache;
use BeSimple\SoapCommon\Converter\DateTimeTypeConverter;
use BeSimple\SoapCommon\Converter\DateTypeConverter;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;
use BeSimple\SoapClient\SoapClient;

class SoapClientTest extends \PHPUnit_Framework_TestCase
{
    public function testSetOptions()
    {
        $soapClient = new SoapClient('foo.wsdl');
        $options = array(
            'cache_type' => Cache::TYPE_DISK_MEMORY,
            'debug'      => true,
            'namespace'  => 'foo',
        );
        $soapClient->setOptions($options);

        $this->assertEquals($options, $soapClient->getOptions());
    }

    public function testSetOptionsThrowsAnExceptionIfOptionsDoesNotExists()
    {
        $soapClient = new SoapClient('foo.wsdl');

        $this->setExpectedException('InvalidArgumentException');
        $soapClient->setOptions(array('bad_option' => true));
    }

    public function testSetOption()
    {
        $soapClient = new SoapClient('foo.wsdl');
        $soapClient->setOption('debug', true);

        $this->assertEquals(true, $soapClient->getOption('debug'));
    }

    public function testSetOptionThrowsAnExceptionIfOptionDoesNotExists()
    {
        $soapClient = new SoapClient('foo.wsdl');

        $this->setExpectedException('InvalidArgumentException');
        $soapClient->setOption('bad_option', 'bar');
    }

    public function testGetOptionThrowsAnExceptionIfOptionDoesNotExists()
    {
        $soapClient = new SoapClient('foo.wsdl');

        $this->setExpectedException('InvalidArgumentException');
        $soapClient->getOption('bad_option');
    }

    public function testCreateSoapHeader()
    {
        $soapClient = new SoapClient('foo.wsdl', array('namespace' => 'http://foobar/soap/User/1.0/'));
        $soapHeader = $soapClient->createSoapHeader('foo', 'bar');

        $this->assertInstanceOf('SoapHeader', $soapHeader);
        $this->assertEquals('http://foobar/soap/User/1.0/', $soapHeader->namespace);
        $this->assertEquals('foo', $soapHeader->name);
        $this->assertEquals('bar', $soapHeader->data);
    }

    public function testCreateSoapHeaderThrowsAnExceptionIfNamespaceIsNull()
    {
        $soapClient = new SoapClient('foo.wsdl');

        $this->setExpectedException('RuntimeException');
        $soapHeader = $soapClient->createSoapHeader('foo', 'bar');
    }

    public function testGetSoapOptions()
    {
        Cache::setType(Cache::TYPE_MEMORY);
        $soapClient = new SoapClient('foo.wsdl', array('debug' => true));
        $this->assertEquals(array('cache_wsdl' => Cache::getType(), 'trace' => true, 'typemap' => array()), $soapClient->getSoapOptions());

        $soapClient = new SoapClient('foo.wsdl', array('debug' => false, 'cache_type' => Cache::TYPE_NONE));
        $this->assertEquals(array('cache_wsdl' => Cache::TYPE_NONE, 'trace' => false, 'typemap' => array()), $soapClient->getSoapOptions());
    }

    public function testGetSoapOptionsWithTypemap()
    {
        $converters = new TypeConverterCollection();

        $dateTimeTypeConverter = new DateTimeTypeConverter();
        $converters->add($dateTimeTypeConverter);

        $dateTypeConverter = new DateTypeConverter();
        $converters->add($dateTypeConverter);

        $soapClient  = new SoapClient('foo.wsdl', array(), $converters);
        $soapOptions = $soapClient->getSoapOptions();

        $this->assertEquals('http://www.w3.org/2001/XMLSchema', $soapOptions['typemap'][0]['type_ns']);
        $this->assertEquals('dateTime', $soapOptions['typemap'][0]['type_name']);
        $this->assertInstanceOf('Closure', $soapOptions['typemap'][0]['from_xml']);
        $this->assertInstanceOf('Closure', $soapOptions['typemap'][0]['to_xml']);

        $this->assertEquals('http://www.w3.org/2001/XMLSchema', $soapOptions['typemap'][1]['type_ns']);
        $this->assertEquals('date', $soapOptions['typemap'][1]['type_name']);
        $this->assertInstanceOf('Closure', $soapOptions['typemap'][1]['from_xml']);
        $this->assertInstanceOf('Closure', $soapOptions['typemap'][1]['to_xml']);
    }

    public function testGetNativeSoapClient()
    {
        $soapClient = new SoapClient(__DIR__.'/Fixtures/foobar.wsdl', array('debug' => true));

        $this->assertInstanceOf('SoapClient', $soapClient->getNativeSoapClient());
    }
}