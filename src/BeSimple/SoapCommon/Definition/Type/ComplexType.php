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

use BeSimple\SoapCommon\Definition\Message;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ComplexType extends Message implements TypeInterface
{
    public function __construct($phpType, $xmlType)
    {
        $this->phpType = $phpType;
        $this->xmlType = str_replace('\\', '.', $xmlType);

        parent::__construct($xmlType);
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
