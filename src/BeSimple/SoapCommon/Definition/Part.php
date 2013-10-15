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

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Part
{
    protected $name;
    protected $type;
    protected $nillable;

    public function __construct($name, $type, $nillable = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->setNillable($nillable);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function isNillable()
    {
        return $this->nillable;
    }

    public function setNillable($nillable)
    {
        $this->nillable = (boolean) $nillable;
    }
}
