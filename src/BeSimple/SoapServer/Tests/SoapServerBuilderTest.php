<?php

/*
 * This file is part of the BeSimpleSoapServer.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapServer\Tests;

use BeSimple\SoapServer\SoapServerBuilder;

/**
 * UnitTest for \BeSimple\SoapServer\SoapServerBuilder
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapServerBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testUnconfiguredWsdl()
    {
        $builder = $this->getSoapServerBuilder();

        $this->expectException('InvalidArgumentException');

        $builder->build();
    }

    public function testUnconfiguredHandler()
    {
        $builder = $this->getSoapServerBuilder();
        $builder->withWsdl('my.wsdl');

        $this->expectException('InvalidArgumentException');

        $builder->build();
    }

    public function getSoapServerBuilder()
    {
        return new SoapServerBuilder();
    }
}
