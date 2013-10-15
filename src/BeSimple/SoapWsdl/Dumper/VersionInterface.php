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

namespace BeSimple\SoapWsdl\Dumper;

use BeSimple\SoapCommon\Definition\Method;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
interface VersionInterface
{
    public function getBindingNode();

    public function getServicePortNode();

    public function addOperation(Method $method);

    public function getEncodingStyle();
}
