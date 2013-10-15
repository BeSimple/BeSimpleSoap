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

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Version11 extends AbstractVersion
{
    public function getEncodingStyle()
    {
        return 'http://schemas.xmlsoap.org/soap/encoding/';
    }
}
