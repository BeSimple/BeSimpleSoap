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

namespace BeSimple\Tests\SoapServer;

use BeSimple\SoapServer\SoapServerBuilder;

/**
 * UnitTest for \BeSimple\SoapServer\SoapServerBuilder
 * 
 * @author Christian Kerl
 */
class SoapServerBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testUnconfiguredWsdl()
    {
        $builder = SoapServerBuilder::createEmpty();
        
        try 
        {
            $builder->build();
            
            $this->fail('The SoapServer requires a WSDL file.');
        }
        catch(\InvalidArgumentException $e)
        {
        }
    }
    
    public function testUnconfiguredHandler()
    {
        $builder = SoapServerBuilder::createEmpty();
        $builder->withWsdl('my.wsdl');
        
        try
        {
            $builder->build();
            
            $this->fail('The SoapServer requires a handler.');
        }
        catch(\InvalidArgumentException $e)
        {
        }
    }
}