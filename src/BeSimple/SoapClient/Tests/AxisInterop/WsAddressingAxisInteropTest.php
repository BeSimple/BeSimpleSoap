<?php

/*
 * Deploy "axis_services/version2.aar" to Apache Axis2 to get this example to
 * work.
 *
 * To rebuild the "axis_services/version2.aar" the following steps need to be
 * done to build a working Apache Axis2 version service with SOAP session
 * enabled.
 *
 * 1) Go to $AXIS_HOME/samples/version and edit the following files:
 *
 * resources/META-INF/services.xml:
 * <service name="Version2" scope="soapsession">
 * ...
 * </service>
 *
 * build.xml:
 * replace version.aar with version2.aar
 *
 * 2) Run ant build.xml in "$AXIS_HOME/samples/version"
 *
 */

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\WsAddressingFilter as BeSimpleWsAddressingFilter;

use BeSimple\SoapClient\Tests\AxisInterop\TestCase;

class WsAddressingAxisInteropTest extends TestCase
{
    private $options = array(
        'soap_version' => SOAP_1_2,
        'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
        'proxy_host' => false,
    );

    public function testSession()
    {
        $sc = new BeSimpleSoapClient('http://localhost:8080/axis2/services/Version2?wsdl', $this->options);
        $soapKernel = $sc->getSoapKernel();
        $wsaFilter = new BeSimpleWsAddressingFilter();
        $soapKernel->registerFilter($wsaFilter);

        $wsaFilter->setReplyTo(BeSimpleWsAddressingFilter::ENDPOINT_REFERENCE_ANONYMOUS);
        $wsaFilter->setMessageId();

        $version = $sc->getVersion();

        $soapSessionId1 = $wsaFilter->getReferenceParameter('http://ws.apache.org/namespaces/axis2', 'ServiceGroupId');

        $wsaFilter->addReferenceParameter('http://ws.apache.org/namespaces/axis2', 'axis2', 'ServiceGroupId', $soapSessionId1);

        $version = $sc->getVersion();

        $soapSessionId2 = $wsaFilter->getReferenceParameter('http://ws.apache.org/namespaces/axis2', 'ServiceGroupId');

        $this->assertEquals($soapSessionId1, $soapSessionId2);
    }
}
