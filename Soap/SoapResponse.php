<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Soap;

use Symfony\Component\HttpFoundation\Response;

/**
 * SoapResponse.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapResponse extends Response
{
    protected $soapHeaders;

    protected $soapReturnValue;

    public function __construct($returnValue)
    {
        parent::__construct();

        $this->soapHeaders = array();
        $this->soapReturnValue = $returnValue;
    }

    public function addSoapHeader(\SoapHeader $header)
    {
        $this->soapHeaders[] = $header;
    }

    public function getSoapHeaders()
    {
        return $this->soapHeaders;
    }

    public function getReturnValue()
    {
        return $this->soapReturnValue;
    }
}