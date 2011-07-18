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

    public function getAliasName()
    {
        return 'method';
    }
}