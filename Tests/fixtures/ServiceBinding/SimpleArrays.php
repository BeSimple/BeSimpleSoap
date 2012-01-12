<?php

namespace BeSimple\SoapBundle\Tests\fixtures\ServiceBinding;

class SimpleArrays
{
    public $array1;

    private $array2;
    private $array3;

    public function __construct($array1, $array2, $array3)
    {
        $this->array1 = $array1;
        $this->array2 = $array2;
        $this->array3 = $array3;
    }

    public function getArray2()
    {
        return $this->array2;
    }

    public function getArray3()
    {
        return $this->array3;
    }
}
