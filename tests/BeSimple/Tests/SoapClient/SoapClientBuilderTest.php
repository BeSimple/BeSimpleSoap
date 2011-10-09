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

namespace BeSimple\Tests\SoapCommon\Soap;

use BeSimple\SoapClient\SoapClientBuilder;

class SoapClientBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $defaultOptions = array(
        'features' => 0,
    );

    public function testContruct()
    {
        $options = $this
            ->getSoapBuilder()
            ->getOptions()
        ;

        $this->assertEquals($this->mergeOptions(array()), $options);
    }

    public function testWithTrace()
    {
        $builder = $this->getSoapBuilder();

        $builder->withTrace();
        $this->assertEquals($this->mergeOptions(array('trace' => true)), $builder->getOptions());

        $builder->withTrace(false);
        $this->assertEquals($this->mergeOptions(array('trace' => false)), $builder->getOptions());
    }

    public function testWithExceptions()
    {
        $builder = $this->getSoapBuilder();

        $builder->withExceptions();
        $this->assertEquals($this->mergeOptions(array('exceptions' => true)), $builder->getOptions());

        $builder->withExceptions(false);
        $this->assertEquals($this->mergeOptions(array('exceptions' => false)), $builder->getOptions());
    }

    public function testWithUserAgent()
    {
        $builder = $this->getSoapBuilder();

        $builder->withUserAgent('BeSimpleSoap Test');
        $this->assertEquals($this->mergeOptions(array('user_agent' => 'BeSimpleSoap Test')), $builder->getOptions());
    }

    public function testCreateWithDefaults()
    {
        $builder = SoapClientBuilder::createWithDefaults();

        $this->assertInstanceOf('BeSimple\SoapClient\SoapClientBuilder', $builder);

        $this->assertEquals($this->mergeOptions(array('soap_version' => SOAP_1_2, 'encoding' => 'UTF-8', 'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 'user_agent' => 'BeSimpleSoap')), $builder->getOptions());
    }

    private function getSoapBuilder()
    {
        return new SoapClientBuilder();
    }

    private function mergeOptions(array $options)
    {
        return array_merge($this->defaultOptions, $options);
    }
}