<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition;

use Bundle\WebServiceBundle\Util\Collection;

class Method
{
    private $name;
    private $controller;
    private $arguments;

    public function __construct($name = null, $controller = null, array $arguments = array())
    {
        $this->setName($name);
        $this->setController($controller);
        $this->setArguments($arguments);
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

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setArguments($arguments)
    {
        $this->arguments = new Collection('getName');
        $this->arguments->addAll($arguments);
    }
}