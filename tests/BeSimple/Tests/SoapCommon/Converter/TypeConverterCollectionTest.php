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
class TypeConverterCollectionTest extends \PHPUnit_Framework_TestCase
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
}