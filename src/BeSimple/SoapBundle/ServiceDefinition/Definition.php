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

use BeSimple\SoapCommon\Definition\Definition as BaseDefinition;
use BeSimple\SoapCommon\Definition\Type\TypeRepository;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Definition extends BaseDefinition
{
    public function __construct(TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;

        $this->setOptions(array());
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }
}
