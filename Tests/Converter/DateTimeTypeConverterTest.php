<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Tests\ServiceBinding;

use BeSimple\SoapBundle\Soap\SoapRequest;
use BeSimple\SoapBundle\Soap\SoapResponse;
use BeSimple\SoapBundle\Converter\DateTimeTypeConverter;

/**
 * UnitTest for \BeSimple\SoapBundle\Converter\DateTimeTypeConverter.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class DateTimeTypeConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertXmlToPhp()
    {
        $converter = new DateTimeTypeConverter();
        $dateXml = '<sometag>2002-10-10T12:00:00-05:00</sometag>';

        $date = $converter->convertXmlToPhp(new SoapRequest(), $dateXml);

        $this->assertEquals(new \DateTime('2002-10-10T12:00:00-05:00'), $date);
    }

    public function testConvertPhpToXml()
    {
        $converter = new DateTimeTypeConverter();
        $date = new \DateTime('2002-10-10T12:00:00-05:00');

        $dateXml = $converter->convertPhpToXml(new SoapResponse(), $date);

        $this->assertEquals('<dateTime>2002-10-10T12:00:00-05:00</dateTime>', $dateXml);
    }
}
