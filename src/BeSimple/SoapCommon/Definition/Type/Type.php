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

namespace BeSimple\SoapCommon\Definition\Type;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Type implements TypeInterface
{
    protected $phpType;
    protected $xmlType;

    public function __construct($phpType, $xmlType)
    {
        $this->phpType = $phpType;
        $this->xmlType = $xmlType;
    }

    public function getPhpType()
    {
        return $this->phpType;
    }

    public function getXmlType()
    {
        return $this->xmlType;
    }
}
