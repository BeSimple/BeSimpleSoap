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
interface TypeInterface
{
    public function getPhpType();

    public function getXmlType();
}
