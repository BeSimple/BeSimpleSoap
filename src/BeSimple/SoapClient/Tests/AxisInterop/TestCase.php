<?php

namespace BeSimple\SoapClient\Tests\AxisInterop;

class TestCase extends \PHPUnit\Framework\TestCase
{
    // when using the SetUpTearDownTrait, methods like doSetup() can
    // be defined with and without the 'void' return type, as you wish
    use \Symfony\Bridge\PhpUnit\SetUpTearDownTrait;

    protected function doSetUp()
    {
        $ch = curl_init('http://localhost:8080/');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (curl_exec($ch) === false) {
            $this->markTestSkipped(
                'The Axis server is not started on port 8080.'
            );
        }

        curl_close($ch);
    }
}