<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 * (c) Andreas Schamberger <mail@andreass.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon;

use BeSimple\SoapCommon\SoapRequest;

/**
 * SOAP request filter interface.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
interface SoapRequestFilter
{
    /**
     * Modify SOAP response.
     *
     * @param SoapRequest $request SOAP request
     */
    public function filterRequest(SoapRequest $request);
}