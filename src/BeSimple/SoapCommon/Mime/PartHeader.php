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

/**
 * Mime part base class.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
abstract class PartHeader
{
    /**
     * Mime headers.
     *
     * @var array(string=>mixed|array(mixed))
     */
    protected $headers = array();

    /**
     * Add a new header to the mime part.
     *
     * @param string $name     Header name
     * @param string $value    Header value
     * @param string $subValue Is sub value?
     *
     * @return void
     */
    public function setHeader($name, $value, $subValue = null)
    {
        if (isset($this->headers[$name]) && !is_null($subValue)) {
            if (!is_array($this->headers[$name])) {
                $this->headers[$name] = array(
                    '@'    => $this->headers[$name],
                    $value => $subValue,
                );
            } else {
                $this->headers[$name][$value] = $subValue;
            }
        } elseif (isset($this->headers[$name]) && is_array($this->headers[$name]) && isset($this->headers[$name]['@'])) {
            $this->headers[$name]['@'] = $value;
        } else {
            $this->headers[$name] = $value;
        }
    }

    /**
     * Get given mime header.
     *
     * @param string $name     Header name
     * @param string $subValue Sub value name
     *
     * @return mixed|array(mixed)
     */
    public function getHeader($name, $subValue = null)
    {
        if (isset($this->headers[$name])) {
            if (!is_null($subValue)) {
                if (is_array($this->headers[$name]) && isset($this->headers[$name][$subValue])) {
                    return $this->headers[$name][$subValue];
                } else {
                    return null;
                }
            } elseif (is_array($this->headers[$name]) && isset($this->headers[$name]['@'])) {
                return $this->headers[$name]['@'];
            } else {
                return $this->headers[$name];
            }
        }
        return null;
    }

    /**
     * Generate headers.
     *
     * @return string
     */
    protected function generateHeaders()
    {
        $charset = strtolower($this->getHeader('Content-Type', 'charset'));
        $preferences = array(
            'scheme' => 'Q',
            'input-charset' => 'utf-8',
            'output-charset' => $charset,
        );
        $headers = '';
        foreach ($this->headers as $fieldName => $value) {
            $fieldValue = $this->generateHeaderFieldValue($value);
            // do not use proper encoding as Apache Axis does not understand this
            // $headers .= iconv_mime_encode($field_name, $field_value, $preferences) . "\r\n";
            $headers .= $fieldName . ': ' . $fieldValue . "\r\n";
        }
        return $headers;
    }

    /**
     * Generates a header field value from the given value paramater.
     *
     * @param array(string=>string)|string $value Header value
     *
     * @return string
     */
    protected function generateHeaderFieldValue($value)
    {
        $fieldValue = '';
        if (is_array($value)) {
            if (isset($value['@'])) {
                $fieldValue .= $value['@'];
            }
            foreach ($value as $subName => $subValue) {
                if ($subName != '@') {
                    $fieldValue .= '; ' . $subName . '=' . $this->quoteValueString($subValue);
                }
            }
        } else {
            $fieldValue .= $value;
        }
        return $fieldValue;
    }

    /**
     * Quote string with '"' if it contains one of the special characters:
     * "(" / ")" / "<" / ">" / "@" / "," / ";" / ":" / "\" / <"> / "/" / "[" / "]" / "?" / "="
     *
     * @param string $string String to quote
     *
     * @return string
     */
    private function quoteValueString($string)
    {
        if (preg_match('~[()<>@,;:\\"/\[\]?=]~', $string)) {
            return '"' . $string . '"';
        } else {
            return $string;
        }
    }
}