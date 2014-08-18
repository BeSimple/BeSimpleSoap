<?php

use BeSimple\SoapClient\SoapClient as BeSimpleSoapClient;
use BeSimple\SoapClient\WsSecurityFilter as BeSimpleWsSecurityFilter;

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation;

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures;

use BeSimple\SoapClient\Tests\ServerInterop\TestCase;

class WsSecurityUserPassServerInteropTest extends TestCase
{
    private $options = array(
        'soap_version' => SOAP_1_2,
        'features'     => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
        'classmap'     => array(
            'getBook'                => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook',
            'getBookResponse'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse',
            'getBooksByType'         => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType',
            'getBooksByTypeResponse' => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse',
            'addBook'                => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook',
            'addBookResponse'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse',
            'BookInformation'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation',
        ),
        'proxy_host' => false,
    );

    public function testUserPassText()
    {
        $sc = new BeSimpleSoapClient(__DIR__.'/Fixtures/WsSecurityUserPass.wsdl', $this->options);

        $wssFilter = new BeSimpleWsSecurityFilter(true, 600);
        $wssFilter->addUserData('libuser', 'books', BeSimpleWsSecurityFilter::PASSWORD_TYPE_TEXT);

        $soapKernel = $sc->getSoapKernel();
        $soapKernel->registerFilter($wssFilter);

        $gb = new getBook();
        $gb->isbn = '0061020052';
        $result = $sc->getBook($gb);
        $this->assertInstanceOf('BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation', $result->getBookReturn);

        $ab = new addBook();
        $ab->isbn = '0445203498';
        $ab->title = 'The Dragon Never Sleeps';
        $ab->author = 'Cook, Glen';
        $ab->type = 'scifi';

        $this->assertTrue((bool) $sc->addBook($ab));

        // getBooksByType("scifi");
    }

    public function testUserPassDigest()
    {
        $sc = new BeSimpleSoapClient(__DIR__.'/Fixtures/WsSecurityUserPass.wsdl', $this->options);

        $wssFilter = new BeSimpleWsSecurityFilter(true, 600);
        $wssFilter->addUserData( 'libuser', 'books', BeSimpleWsSecurityFilter::PASSWORD_TYPE_DIGEST );

        $soapKernel = $sc->getSoapKernel();
        $soapKernel->registerFilter($wssFilter);

        $gb = new getBook();
        $gb->isbn = '0061020052';
        $result = $sc->getBook($gb);
        $this->assertInstanceOf('BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation', $result->getBookReturn);

        $ab = new addBook();
        $ab->isbn = '0445203498';
        $ab->title = 'The Dragon Never Sleeps';
        $ab->author = 'Cook, Glen';
        $ab->type = 'scifi';

        $this->assertTrue((bool) $sc->addBook($ab));

        // getBooksByType("scifi");
    }
}
