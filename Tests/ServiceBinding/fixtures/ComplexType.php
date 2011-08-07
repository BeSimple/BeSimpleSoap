<?php

namespace BeSimple\SoapBundle\Tests\ServiceBinding\fixtures;

class ComplexType
{
    public $bar;

    private $foo;

    public function getFoo()
    {
        return $this->foo;
    }

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }
}