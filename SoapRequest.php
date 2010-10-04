<?php

namespace Bundle\WebServiceBundle;

use Symfony\Component\HttpFoundation\Request;

class SoapRequest extends Request
{
    protected $soapAction;

    protected $soapHeader;

    protected $soapParameter;
}