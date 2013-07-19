<?php

/*
 * This file is part of BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Mime;

use BeSimple\SoapCommon\Helper;

/**
 * Mime multi part container.
 *
 * Headers:
 * - MIME-Version
 * - Content-Type
 * - Content-ID
 * - Content-Location
 * - Content-Description
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class MultiPart extends PartHeader
{
    /**
     * Content-ID of main part.
     *
     * @var string
     */
    protected $mainPartContentId;

    /**
     * Mime parts.
     *
     * @var array(\BeSimple\SoapCommon\Mime\Part)
     */
    protected $parts = array();

    /**
     * Construct new mime object.
     *
     * @param string $boundary Boundary string
     *
     * @return void
     */
    public function __construct($boundary = null)
    {
        $this->setHeader('MIME-Version', '1.0');
        $this->setHeader('Content-Type', 'multipart/related');
        $this->setHeader('Content-Type', 'type', 'text/xml');
        $this->setHeader('Content-Type', 'charset', 'utf-8');
        if (is_null($boundary)) {
            $boundary = $this->generateBoundary();
        }
        $this->setHeader('Content-Type', 'boundary', $boundary);
    }

    /**
     * Get mime message of this object (without headers).
     *
     * @param boolean $withHeaders Returned mime message contains headers
     *
     * @return string
     */
    public function getMimeMessage($withHeaders = false)
    {
        $message = ($withHeaders === true) ? $this->generateHeaders() : "";
        // add parts
        foreach ($this->parts as $part) {
            $message .= "\r\n" . '--' . $this->getHeader('Content-Type', 'boundary') . "\r\n";
            $message .= $part->getMessagePart();
        }
        $message .= "\r\n" . '--' . $this->getHeader('Content-Type', 'boundary') . '--';
        return $message;
    }

    /**
     * Get string array with MIME headers for usage in HTTP header (with CURL).
     * Only 'Content-Type' and 'Content-Description' headers are returned.
     *
     * @return arrray(string)
     */
    public function getHeadersForHttp()
    {
        $allowed = array(
            'Content-Type',
            'Content-Description',
        );
        $headers = array();
        foreach ($this->headers as $fieldName => $value) {
            if (in_array($fieldName, $allowed)) {
                $fieldValue = $this->generateHeaderFieldValue($value);
                // for http only ISO-8859-1
                $headers[] = $fieldName . ': '. iconv('utf-8', 'ISO-8859-1//TRANSLIT', $fieldValue);
            }
        }
        return $headers;
    }

    /**
     * Add new part to MIME message.
     *
     * @param \BeSimple\SoapCommon\Mime\Part $part   Part that is added
     * @param boolean                        $isMain Is the given part the main part of mime message
     *
     * @return void
     */
    public function addPart(Part $part, $isMain = false)
    {
        $contentId = trim($part->getHeader('Content-ID'), '<>');
        if ($isMain === true) {
            $this->mainPartContentId = $contentId;
            $this->setHeader('Content-Type', 'start', $part->getHeader('Content-ID'));
        }
        $this->parts[$contentId] = $part;
    }

    /**
     * Get part with given content id. If there is no content id given it
     * returns the main part that is defined through the content-id start
     * parameter.
     *
     * @param string $contentId Content id of desired part
     *
     * @return \BeSimple\SoapCommon\Mime\Part|null
     */
    public function getPart($contentId = null)
    {
        if (is_null($contentId)) {
            $contentId = $this->mainPartContentId;
        }
        if (isset($this->parts[$contentId])) {
            return $this->parts[$contentId];
        }
        return null;
    }

    /**
     * Get all parts.
     *
     * @param boolean $includeMainPart Should main part be in result set
     *
     * @return array(\BeSimple\SoapCommon\Mime\Part)
     */
    public function getParts($includeMainPart = false)
    {
        if ($includeMainPart === true) {
            $parts = $this->parts;
        } else {
            $parts = array();
            foreach ($this->parts as $cid => $part) {
                if ($cid != $this->mainPartContentId) {
                    $parts[$cid] = $part;
                }
            }
        }
        return $parts;
    }

    /**
     * Returns a unique boundary string.
     *
     * @return string
     */
    protected function generateBoundary()
    {
        return 'urn:uuid:' . Helper::generateUUID();
    }
}