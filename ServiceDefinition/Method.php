<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition;

use BeSimple\SoapBundle\Util\Collection;

class Method
{
    private $name;
    private $controller;
    private $arguments;
    private $headers;
    private $return;

    public function __construct($name = null, $controller = null, array $headers = array(), array $arguments = array(), Type $return = null)
    {
        $this->setName($name);
        $this->setController($controller);
        $this->setHeaders($headers);
        $this->setArguments($arguments);

        if ($return) {
            $this->setReturn($return);
        }
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

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = new Collection('getName', 'BeSimple\SoapBundle\ServiceDefinition\Header');
        $this->headers->addAll($headers);
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setArguments(array $arguments)
    {
        $this->arguments = new Collection('getName', 'BeSimple\SoapBundle\ServiceDefinition\Argument');
        $this->arguments->addAll($arguments);
    }

    public function getReturn()
    {
        return $this->return;
    }

    public function setReturn(Type $return)
    {
        $this->return = $return;
    }
}