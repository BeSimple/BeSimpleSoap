<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Tests\Converter;

use BeSimple\SoapBundle\Soap\SoapRequest;
use BeSimple\SoapBundle\Soap\SoapResponse;
use BeSimple\SoapBundle\Converter\DateTypeConverter;

/**
 * UnitTest for \BeSimple\SoapBundle\Converter\DateTimeTypeConverter.
 */
class DateTypeConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertXmlToPhp()
    {
        $converter = new DateTypeConverter();

        $dateXml = '<sometag>2002-10-10</sometag>';
        $date = $converter->convertXmlToPhp(new SoapRequest(), $dateXml);

        $this->assertEquals(new \DateTime('2002-10-10'), $date);
    }

    public function testConvertPhpToXml()
    {
        $converter = new DateTypeConverter();

        $date    = new \DateTime('2002-10-10');
        $dateXml = $converter->convertPhpToXml(new SoapResponse(), $date);

        $this->assertEquals('<date>2002-10-10</date>', $dateXml);
    }
}
