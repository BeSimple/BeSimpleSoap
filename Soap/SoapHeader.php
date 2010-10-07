<?php

namespace Bundle\WebServiceBundle\Soap;

class SoapHeader
{
    private $namespace;
    private $name;
    private $data;

    public function __construct($namespace, $name, $data)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->data = $data;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getData()
    {
        return $this->data;
    }

    public function toNativeSoapHeader()
    {
        return new \SoapHeader($this->namespace, $this->name, $this->data);
    }
}