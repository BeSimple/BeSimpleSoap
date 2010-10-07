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

use Bundle\WebServiceBundle\Util\Collection;

use Symfony\Component\HttpFoundation\Response;

/**
 * SoapResponse.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapResponse extends Response
{
    /**
     * @var \Bundle\WebServiceBundle\Util\Collection
     */
    protected $soapHeaders;

    protected $soapReturnValue;

    public function __construct($returnValue = null)
    {
        parent::__construct();

        $this->soapHeaders = new Collection('getName');
        $this->setReturnValue($returnValue);
    }

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