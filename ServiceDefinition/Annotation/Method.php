<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Annotation;

class Method
{
    private $name;
    private $service;
    
    public function __construct($values)
    {
        $this->name = isset($values['value']) ? $values['value'] : null;
        $this->service = isset($values['service']) ? $values['service'] : null;
    }
    
    public function getName($default = null)
    {
        return $this->name !== null ? $this->name : $default;
    }
    
    public function getService()
    {
        return $this->service;
    }
}