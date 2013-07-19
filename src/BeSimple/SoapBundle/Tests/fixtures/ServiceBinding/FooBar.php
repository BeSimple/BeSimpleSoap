<?php

namespace BeSimple\SoapBundle\Tests\fixtures\ServiceBinding;

class FooBar
{
    protected $foo;
    protected $bar;

    public function __construct(Foo $foo, Bar $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
