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

namespace BeSimple\SoapCommon\Definition;

use BeSimple\SoapCommon\Definition\Type\TypeInterface;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Message
{
    protected $name;
    protected $parts;

    public function __construct($name)
    {
        $this->name = $name;
        $this->parts = array();
    }

    public function getName()
    {
        return $this->name;
    }

    public function all()
    {
        return $this->parts;
    }

    public function get($name, $default = null)
    {
        return isset($this->parts[$name]) ? $this->parts[$name] : $default;
    }

    public function isEmpty()
    {
        return 0 === count($this->parts) ? true : false;
    }

    public function add($name, $phpType, $nillable = false, $attribute = false)
    {
        if ($phpType instanceof TypeInterface) {
            $phpType = $phpType->getPhpType();
        }

        $this->parts[$name] = new Part($name, $phpType, $nillable, $attribute);

        return $this;
    }
}
