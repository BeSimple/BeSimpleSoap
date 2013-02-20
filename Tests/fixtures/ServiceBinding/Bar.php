<?php

namespace BeSimple\SoapBundle\Tests\fixtures\ServiceBinding;

class Bar
{
    private $foo;

    private $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
