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

use BeSimple\SoapCommon\Converter\DateTypeConverter;

/**
 * UnitTest for \BeSimple\SoapCommon\Converter\DateTypeConverter.
 */
class DateTypeConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertXmlToPhp()
    {
        $converter = new DateTypeConverter();

        $dateXml = '<sometag>2002-10-10</sometag>';
        $date = $converter->convertXmlToPhp($dateXml);

        $this->assertEquals(new \DateTime('2002-10-10'), $date);
    }

    public function testConvertPhpToXml()
    {
        $converter = new DateTypeConverter();

        $date    = new \DateTime('2002-10-10');
        $dateXml = $converter->convertPhpToXml($date);

        $this->assertEquals('<date>2002-10-10</date>', $dateXml);
    }

    public function testConvertNullDateTimeXmlToPhp()
    {
        $converter = new DateTypeConverter();

        $dateXml = '<sometag xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"/>';
        $date = $converter->convertXmlToPhp($dateXml);

        $this->assertNull($date);
    }
}

