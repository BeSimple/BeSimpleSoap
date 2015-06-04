<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Converter;

use BeSimple\SoapBundle\Soap\SoapRequest;

/**
 * Request aware interface.
 */
interface RequestAwareInterface
{
    /**
     * Set request.
     *
     * @param SoapRequest $request
     */
    function setRequest(SoapRequest $request);
}
