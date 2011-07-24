<?php

namespace BeSimple\SoapBundle\Tests\ServiceBinding\fixtures;

class Foo
{
    public $bar;

    public function __construct($bar = null)
    {
        $this->bar = $bar;
    }
}