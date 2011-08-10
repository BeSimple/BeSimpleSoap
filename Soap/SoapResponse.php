<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Soap;

use BeSimple\SoapBundle\Util\Collection;

use Symfony\Component\HttpFoundation\Response;

/**
 * SoapResponse.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapResponse extends Response
{
    /**
     * @var \BeSimple\SoapBundle\Util\Collection
     */
    protected $soapHeaders;

    /**
     * @var mixed
     */
    protected $soapReturnValue;

    public function __construct($returnValue = null)
    {
        parent::__construct();

        $this->soapHeaders = new Collection('getName', 'BeSimple\SoapBundle\Soap\SoapHeader');
        $this->setReturnValue($returnValue);
    }

    /**
     * @param SoapHeader $soapHeader
     */
    public function addSoapHeader(SoapHeader $soapHeader)
    {
        $this->soapHeaders->add($soapHeader);
    }

    /**
     * @return \BeSimple\SoapBundle\Util\Collection
     */
    public function getSoapHeaders()
    {
        return $this->soapHeaders;
    }

    public function setReturnValue($value)
    {
        $this->soapReturnValue = $value;
    }

    public function getReturnValue()
    {
        return $this->soapReturnValue;
    }
}