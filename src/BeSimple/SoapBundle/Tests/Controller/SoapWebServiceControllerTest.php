<?php

namespace BeSimple\SoapBundle\Tests\Controller;

use BeSimple\SoapBundle\Controller\SoapWebServiceController;
use BeSimple\SoapBundle\Soap\SoapRequest;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use ReflectionClass;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
class SoapWebServiceControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SoapWebServiceController
     */
    private $controller;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var SoapRequest
     */
    private $soapRequest;

    protected function setUp()
    {
        $serviceBinder = $this->getMockBuilder('BeSimple\SoapBundle\ServiceBinding\ServiceBinder')
            ->disableOriginalConstructor()
            ->getMock();

        $serviceBinder->expects($this->any())
            ->method('isServiceMethod')
            ->will($this->returnValue(true));

        $serviceBinder->expects($this->any())
            ->method('processServiceMethodArguments')
            ->will($this->returnValue(array()));

        $this->soapRequest = new SoapRequest();
        $this->soapRequest->attributes = new ParameterBag();

        $this->controller = new SoapWebServiceController();

        $this->setControllerProperty('serviceBinder', $serviceBinder);
        $this->setControllerProperty('soapRequest', $this->soapRequest);

        $this->container = new Container();
        $this->controller->setContainer($this->container);
    }

    /**
     * @param string $property
     * @param mixed  $value
     */
    private function setControllerProperty($property, $value)
    {
        $class = new ReflectionClass($this->controller);

        $prop = $class->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($this->controller, $value);
        $prop->setAccessible(false);
    }

    public function testExceptionOnCallDispatchesExceptionEvent()
    {
        $httpKernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->container->set('http_kernel', $httpKernel);
        $this->container->set('event_dispatcher', $eventDispatcher);

        $exception = new \SoapFault('faultcode', 'faultstring');

        $httpKernel->expects($this->once())
            ->method('handle')
            ->will($this->throwException($exception));

        $event = new GetResponseForExceptionEvent(
            $httpKernel,
            $this->soapRequest,
            HttpKernelInterface::SUB_REQUEST,
            $exception
        );

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(KernelEvents::EXCEPTION, $event);

        $this->setExpectedException('SoapFault');

        $this->controller->foo();
    }
}
