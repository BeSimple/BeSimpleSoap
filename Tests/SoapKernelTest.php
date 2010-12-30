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

use Bundle\WebServiceBundle\Converter\ConverterRepository;

use Symfony\Component\HttpFoundation\Request;

use Bundle\WebServiceBundle\SoapKernel;
use Bundle\WebServiceBundle\Soap\SoapRequest;
use Bundle\WebServiceBundle\Soap\SoapResponse;

use Bundle\WebServiceBundle\ServiceBinding\ServiceBinder;
use Bundle\WebServiceBundle\ServiceBinding\RpcLiteralResponseMessageBinder;
use Bundle\WebServiceBundle\ServiceBinding\RpcLiteralRequestMessageBinder;

use Bundle\WebServiceBundle\ServiceDefinition\ServiceDefinition;
use Bundle\WebServiceBundle\ServiceDefinition\Method;
use Bundle\WebServiceBundle\ServiceDefinition\Loader\XmlFileLoader;

/**
 * UnitTest for \Bundle\WebServiceBundle\SoapKernel.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapKernelTest extends \PHPUnit_Framework_TestCase
{
    private static $soapRequestContent = '<?xml version="1.0"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost/"><soapenv:Header/><soapenv:Body><ns1:math_multiply><a>10</a><b>20</b></ns1:math_multiply></soapenv:Body></soapenv:Envelope>';
    private static $soapResponseContent = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost/"><SOAP-ENV:Body><ns1:math_multiplyResponse><result>200</result></ns1:math_multiplyResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>';

    private $soapKernel;

    public function setUp()
    {
        $serviceDefinition = new ServiceDefinition('api');
        $serviceDefinitionLoader = new XmlFileLoader(__DIR__ . '/fixtures/api-servicedefinition.xml');
        $serviceDefinitionDumper = new StaticFileDumper(__DIR__ . '/fixtures/api.wsdl');
        $requestMessageBinder = new RpcLiteralRequestMessageBinder();
        $responseMessageBinder = new RpcLiteralResponseMessageBinder();

        $serviceBinder = new ServiceBinder($serviceDefinition, $serviceDefinitionLoader, $serviceDefinitionDumper, $requestMessageBinder, $responseMessageBinder);

        $converterRepository = new ConverterRepository();

        $httpKernel = $this->getMock('Symfony\\Component\\HttpKernel\\HttpKernelInterface');
        $httpKernel->expects($this->any())
                   ->method('handle')
                   ->will($this->returnValue(new SoapResponse(200)));

        $this->soapKernel = new SoapKernel($serviceBinder, $converterRepository, $httpKernel);
    }

    public function testHandle()
    {
        $response = $this->soapKernel->handle(new SoapRequest(self::$soapRequestContent));

        $this->assertEquals(200, $response->getReturnValue());
        $this->assertXmlStringEqualsXmlString(self::$soapResponseContent, $response->getContent());
    }

    public function testHandleWithInvalidRequest()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->soapKernel->handle(new Request());
    }
}