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

namespace BeSimple\SoapServer\Exception;

/**
 * ReceiverSoapFault send a "Receiver" fault code to client.
 * This fault code is standardized: http://www.w3.org/TR/soap12-part1/#tabsoapfaultcodes
 */
class ReceiverSoapFault extends \SoapFault
{
    public function __construct($faultstring, $faultactor = null, $detail = null, $faultname = null, $headerfault = null)
    {
        parent::__construct('Receiver', $faultstring, $faultactor, $detail, $faultname, $headerfault);
    }
}
