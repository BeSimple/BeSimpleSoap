<?php

namespace BeSimple\SoapClient\Tests\AxisInterop;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
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