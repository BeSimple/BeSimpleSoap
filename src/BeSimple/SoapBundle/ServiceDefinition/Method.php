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

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Method extends BaseMethod
{
    private $controller;
    private $version;

    public function __construct($name, $controller, $soapAction, $soapRequiredAction, $version = \SOAP_1_1)
    {
        parent::__construct($name);

        $this->controller = $controller;
        $this->addOption('soapAction', $soapAction);
        $this->addOption('soapActionRequired', $soapRequiredAction);
        $this->version = $version;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getVersions()
    {
        return array($this->version);
    }
}
