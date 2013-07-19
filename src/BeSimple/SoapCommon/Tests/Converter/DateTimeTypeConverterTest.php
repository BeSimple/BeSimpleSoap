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

use BeSimple\SoapCommon\Converter\DateTimeTypeConverter;

/**
 * UnitTest for \BeSimple\SoapCommon\Converter\DateTimeTypeConverter.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class DateTimeTypeConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertXmlToPhp()
    {
        $converter = new DateTimeTypeConverter();

        $dateXml = '<sometag>2002-10-10T12:00:00-05:00</sometag>';
        $date = $converter->convertXmlToPhp($dateXml);

        $this->assertEquals(new \DateTime('2002-10-10T12:00:00-05:00'), $date);
    }

    public function testConvertPhpToXml()
    {
        $converter = new DateTimeTypeConverter();

        $date    = new \DateTime('2002-10-10T12:00:00-05:00');
        $dateXml = $converter->convertPhpToXml($date);

        $this->assertEquals('<dateTime>2002-10-10T12:00:00-05:00</dateTime>', $dateXml);
    }

    public function testConvertNullDateTimeXmlToPhp()
    {
        $converter = new DateTimeTypeConverter();

        $dateXml = '<sometag xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true"/>';
        $date = $converter->convertXmlToPhp($dateXml);

        $this->assertNull($date);
    }
}

