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

namespace BeSimple\SoapServer;

use BeSimple\SoapCommon\SoapKernel as CommonSoapKernel;
use BeSimple\SoapCommon\SoapRequest as CommonSoapRequest;
use BeSimple\SoapCommon\SoapResponse as CommonSoapResponse;

/**
 * SoapKernel for Server.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class SoapKernel extends CommonSoapKernel
{
    /**
     * {@inheritDoc}
     */
    public function filterRequest(CommonSoapRequest $request)
    {
        parent::filterRequest($request);

        $this->attachments = $request->getAttachments();
    }

    /**
     * {@inheritDoc}
     */
    public function filterResponse(CommonSoapResponse $response)
    {
        $response->setAttachments($this->attachments);
        $this->attachments = array();

        parent::filterResponse($response);
    }
}
