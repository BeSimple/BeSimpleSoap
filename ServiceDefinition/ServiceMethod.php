<?php

namespace Bundle\WebServiceBundle\ServiceDefinition;

class ServiceMethod
{
    private $name;
    private $controller;

    public function __construct($name = null, $controller = null)
    {
        $this->setName($name);
        $this->setController($controller);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }
}