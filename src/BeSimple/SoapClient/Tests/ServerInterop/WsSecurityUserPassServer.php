<?php

require '../../../../../vendor/autoload.php';

use BeSimple\SoapCommon\Helper as BeSimpleSoapHelper;
use BeSimple\SoapServer\SoapServer as BeSimpleSoapServer;
use BeSimple\SoapServer\WsSecurityFilter as BeSimpleWsSecurityFilter;

use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse;
use BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation;

$options = array(
    'soap_version'    => SOAP_1_1,
    'features'        => SOAP_SINGLE_ELEMENT_ARRAYS, // make sure that result is array for size=1
    'cache_wsdl'      => WSDL_CACHE_NONE,
    'classmap'        => array(
        'getBook'                => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBook',
        'getBookResponse'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBookResponse',
        'getBooksByType'         => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByType',
        'getBooksByTypeResponse' => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\getBooksByTypeResponse',
        'addBook'                => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBook',
        'addBookResponse'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\addBookResponse',
        'BookInformation'        => 'BeSimple\SoapClient\Tests\ServerInterop\Fixtures\BookInformation',
    ),
);

class Auth
{
    public static function usernamePasswordCallback($user)
    {
        if ($user == 'libuser') {
            return 'books';
        }

        return null;
    }
}

class WsSecurityUserPassServer
{
    public function getBook(getBook $gb)
    {
        $bi = new BookInformation();
        $bi->isbn = $gb->isbn;
        $bi->title = 'title';
        $bi->author = 'author';
        $bi->type = 'scifi';

        $br = new getBookResponse();
        $br->getBookReturn = $bi;

        return $br;
    }

    public function addBook(addBook $ab)
    {
        $abr = new addBookResponse();
        $abr->addBookReturn = true;

        return $abr;
    }
}

$ss = new BeSimpleSoapServer(__DIR__.'/Fixtures/WsSecurityUserPass.wsdl', $options);

$wssFilter = new BeSimpleWsSecurityFilter();
$wssFilter->setUsernamePasswordCallback(array('Auth', 'usernamePasswordCallback'));

$soapKernel = $ss->getSoapKernel();
$soapKernel->registerFilter($wssFilter);

$ss->setClass('WsSecurityUserPassServer');
$ss->handle();
