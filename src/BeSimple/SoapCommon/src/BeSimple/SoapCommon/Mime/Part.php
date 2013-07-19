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
 * Mime part. Everything must be UTF-8. Default charset for text is UTF-8.
 *
 * Headers:
 * - Content-Type
 * - Content-Transfer-Encoding
 * - Content-ID
 * - Content-Location
 * - Content-Description
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class Part extends PartHeader
{
    /**
     * Encoding type base 64
     */
    const ENCODING_BASE64 = 'base64';

    /**
     * Encoding type binary
     */
    const ENCODING_BINARY = 'binary';

    /**
     * Encoding type eight bit
     */
    const ENCODING_EIGHT_BIT = '8bit';

    /**
     * Encoding type seven bit
     */
    const ENCODING_SEVEN_BIT = '7bit';

    /**
     * Encoding type quoted printable
     */
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    /**
     * Content.
     *
     * @var mixed
     */
    protected $content;

    /**
     * Construct new mime object.
     *
     * @param mixed  $content     Content
     * @param string $contentType Content type
     * @param string $charset     Charset
     * @param string $encoding    Encoding
     * @param string $contentId   Content id
     *
     * @return void
     */
    public function __construct($content = null, $contentType = 'application/octet-stream', $charset = null, $encoding = self::ENCODING_BINARY, $contentId = null)
    {
        $this->content = $content;
        $this->setHeader('Content-Type', $contentType);
        if (!is_null($charset)) {
            $this->setHeader('Content-Type', 'charset', $charset);
        } else {
            $this->setHeader('Content-Type', 'charset', 'utf-8');
        }
        $this->setHeader('Content-Transfer-Encoding', $encoding);
        if (is_null($contentId)) {
            $contentId = $this->generateContentId();
        }
        $this->setHeader('Content-ID', '<' . $contentId . '>');
    }

    /**
     * __toString.
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->content;
    }

    /**
     * Get mime content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set mime content.
     *
     * @param mixed $content Content to set
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get complete mime message of this object.
     *
     * @return string
     */
    public function getMessagePart()
    {
        return $this->generateHeaders() . "\r\n" . $this->generateBody();
    }

    /**
     * Generate body.
     *
     * @return string
     */
    protected function generateBody()
    {
        $encoding = strtolower($this->getHeader('Content-Transfer-Encoding'));
        $charset = strtolower($this->getHeader('Content-Type', 'charset'));
        if ($charset != 'utf-8') {
            $content = iconv('utf-8', $charset . '//TRANSLIT', $this->content);
        } else {
            $content = $this->content;
        }
        switch ($encoding) {
            case self::ENCODING_BASE64:
                return substr(chunk_split(base64_encode($content), 76, "\r\n"), -2);
            case self::ENCODING_QUOTED_PRINTABLE:
                return quoted_printable_encode($content);
            case self::ENCODING_BINARY:
                return $content;
            case self::ENCODING_SEVEN_BIT:
            case self::ENCODING_EIGHT_BIT:
            default:
                return preg_replace("/\r\n|\r|\n/", "\r\n", $content);
        }
    }

    /**
     * Returns a unique ID to be used for the Content-ID header.
     *
     * @return string
     */
    protected function generateContentId()
    {
        return 'urn:uuid:' . Helper::generateUUID();
    }
}