<?php

/*
 * This file is part of the BeSimpleSoapServer.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapServer;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapServer extends \SoapServer
{
    public function __construct($wsdl, array $options = array())
    {
        parent::__construct($wsdl, $options);
    }

    public function handle($soap_request = null)
    {
        parent::handle($soap_request);
    }
}