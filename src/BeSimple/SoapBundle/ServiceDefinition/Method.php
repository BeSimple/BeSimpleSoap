<?php

/*
 * This file is part of the BeSimpleSoap.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition;

use BeSimple\SoapCommon\Definition\Method as BaseMethod;
use BeSimple\SoapCommon\Definition\Type\TypeRepository;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Method extends BaseMethod
{
    private $controller;

    public function __construct($name, $controller)
    {
        parent::__construct($name);

        $this->controller = $controller;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getVersions()
    {
        return array(\SOAP_1_1);
    }
}
