<?php

namespace BeSimple\SoapBundle\Tests\fixtures\ServiceBinding;

class Foo
{
    public $foo;
    public $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
