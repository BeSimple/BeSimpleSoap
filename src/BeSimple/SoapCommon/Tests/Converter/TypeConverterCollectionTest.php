<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Tests\Converter;

use BeSimple\SoapCommon\Converter\TypeConverterCollection;
use BeSimple\SoapCommon\Converter\DateTimeTypeConverter;
use BeSimple\SoapCommon\Converter\DateTypeConverter;

/**
 * UnitTest for \BeSimple\SoapCommon\Converter\TypeConverterCollection.
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class TypeConverterCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testAdd()
    {
        $converters = new TypeConverterCollection();

        $dateTimeTypeConverter = new DateTimeTypeConverter();
        $converters->add($dateTimeTypeConverter);

        $this->assertSame(array($dateTimeTypeConverter), $converters->all());

        $dateTypeConverter = new DateTypeConverter();
        $converters->add($dateTypeConverter);

        $this->assertSame(array($dateTimeTypeConverter, $dateTypeConverter), $converters->all());
    }

    public function testGetTypemap()
    {
        $converters = new TypeConverterCollection();

        $this->assertEquals(array(), $converters->getTypemap());

        $dateTimeTypeConverter = new DateTimeTypeConverter();
        $converters->add($dateTimeTypeConverter);

        $dateTypeConverter = new DateTypeConverter();
        $converters->add($dateTypeConverter);

        $typemap = $converters->getTypemap();

        $this->assertEquals('http://www.w3.org/2001/XMLSchema', $typemap[0]['type_ns']);
        $this->assertEquals('dateTime', $typemap[0]['type_name']);
        $this->assertInstanceOf('Closure', $typemap[0]['from_xml']);
        $this->assertInstanceOf('Closure', $typemap[0]['to_xml']);

        $this->assertEquals('http://www.w3.org/2001/XMLSchema', $typemap[1]['type_ns']);
        $this->assertEquals('date', $typemap[1]['type_name']);
        $this->assertInstanceOf('Closure', $typemap[1]['from_xml']);
        $this->assertInstanceOf('Closure', $typemap[1]['to_xml']);
    }

    public function testSet()
    {
        $converters = new TypeConverterCollection();

        $dateTimeTypeConverter = new DateTimeTypeConverter();
        $converters->add($dateTimeTypeConverter);

        $converter = array(new DateTypeConverter);
        $converters->set($converter);

        $this->assertSame($converter, $converters->all());
    }

    public function testAddCollection()
    {
        $converters1 = new TypeConverterCollection();
        $converters2 = new TypeConverterCollection();

        $dateTimeTypeConverter = new DateTimeTypeConverter();
        $converters2->add($dateTimeTypeConverter);
        $converters1->addCollection($converters2);

        $this->assertSame(array($dateTimeTypeConverter), $converters1->all());

        $this->expectException('InvalidArgumentException');

        $converters1->addCollection($converters2);
    }
}
