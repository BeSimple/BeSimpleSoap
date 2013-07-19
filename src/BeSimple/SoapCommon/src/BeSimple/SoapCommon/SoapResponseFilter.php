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

use BeSimple\SoapCommon\SoapResponse;

/**
 * SOAP response filter interface.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
interface SoapResponseFilter
{
    /**
     * Modify SOAP response.
     *
     * @param SoapResponse $response SOAP response
     */
    public function filterResponse(SoapResponse $response);
}