<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Tests;

use Symfony\Component\HttpFoundation\Request;

use Bundle\WebServiceBundle\SoapKernel;

/**
 * UnitTest for \Bundle\WebServiceBundle\SoapKernel.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapKernelTest extends \PHPUnit_Framework_TestCase
{
    private $soapKernel;

    public function setUp()
    {
        $soapServer = new \SoapServer();

        $this->soapKernel = new SoapKernel($soapServer, null);
    }

	/**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidRequest()
    {
        $this->soapKernel->handle(new Request());
    }
}