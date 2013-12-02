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

use BeSimple\SoapCommon\Mime\Part as MimePart;
use BeSimple\SoapCommon\SoapRequest;
use BeSimple\SoapCommon\SoapResponse;
use BeSimple\SoapCommon\SoapRequestFilter;
use BeSimple\SoapCommon\SoapResponseFilter;

/**
 * SoapKernel provides methods to pre- and post-process SoapRequests and SoapResponses using
 * chains of SoapRequestFilter and SoapResponseFilter objects (roughly following
 * the chain-of-responsibility pattern).
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapKernel
{
    /**
    * Mime attachments.
    *
    * @var array(\BeSimple\SoapCommon\Mime\Part)
    */
    protected $attachments = array();

    /**
     * Request filters.
     *
     * @var array(SoapRequestFilter)
     */
    private $requestFilters = array();

    /**
     * Response filters.
     *
     * @var array(SoapResponseFilter)
     */
    private $responseFilters = array();

    /**
    * Add attachment.
    *
    * @param \BeSimple\SoapCommon\Mime\Part $attachment New attachment
    *
    * @return void
    */
    public function addAttachment(MimePart $attachment)
    {
        $contentId = trim($attachment->getHeader('Content-ID'), '<>');

        $this->attachments[$contentId] = $attachment;
    }

    /**
     * Get attachment and remove from array.
     *
     * @param string $contentId Content ID of attachment
     *
     * @return \BeSimple\SoapCommon\Mime\Part|null
     */
    public function getAttachment($contentId)
    {
        if (isset($this->attachments[$contentId])) {
            $part = $this->attachments[$contentId];
            unset($this->attachments[$contentId]);

            return $part;
        }

        return null;
    }

    /**
     * Registers the given object either as filter for SoapRequests or as filter for SoapResponses
     * or as filter for both depending on the implemented interfaces. Inner filters have to be registered
     * before outer filters. This means the order is as follows: RequestFilter2->RequestFilter1 and
     * ResponseFilter1->ResponseFilter2.
     *
     * TODO: add priority mechanism to ensure correct order of filters
     *
     * @param SoapRequestFilter|SoapResponseFilter $filter Filter to register
     */
    public function registerFilter($filter)
    {
        if ($filter instanceof SoapRequestFilter) {
            array_unshift($this->requestFilters, $filter);
        }

        if ($filter instanceof SoapResponseFilter) {
            array_push($this->responseFilters, $filter);
        }
    }

    /**
     * Applies all registered SoapRequestFilter to the given SoapRequest.
     *
     * @param SoapRequest $request Soap request
     */
    public function filterRequest(SoapRequest $request)
    {
        foreach ($this->requestFilters as $filter) {
            $filter->filterRequest($request);
        }
    }

    /**
     * Applies all registered SoapResponseFilter to the given SoapResponse.
     *
     * @param SoapResponse $response SOAP response
     */
    public function filterResponse(SoapResponse $response)
    {
        foreach ($this->responseFilters as $filter) {
            $filter->filterResponse($response);
        }
    }
}
