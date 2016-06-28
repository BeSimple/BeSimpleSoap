<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Annotation;

/**
 * @Annotation
 */
class Method extends Configuration
{
    private $value;
    private $service;
    private $soapAction;
    private $soapActionRequired;

    public function getValue()
    {
        return $this->value;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getSoapAction()
    {
        return $this->soapAction;
    }

    /**
     * @param string $soapAction
     */
    public function setSoapAction($soapAction)
    {
        $this->soapAction = $soapAction;
    }

    /**
     * @return bool
     */
    public function getSoapActionRequired()
    {
        return $this->soapActionRequired;
    }

    /**
     * @param bool $soapActionRequired
     */
    public function setSoapActionRequired($soapActionRequired)
    {
        $this->soapActionRequired = boolval($soapActionRequired);
    }

    public function getAliasName()
    {
        return 'method';
    }
}