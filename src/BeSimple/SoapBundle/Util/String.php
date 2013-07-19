<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Util;

/**
 * String provides utility methods for strings.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class String
{
    /**
     * Checks if a string starts with a given string.
     *
     * @param  string $str    A string
     * @param  string $substr A string to check against
     *
     * @return bool           True if str starts with substr
     */
    public static function startsWith($str, $substr)
    {
        if(is_string($str) && is_string($substr) && strlen($str) >= strlen($substr)) {
            return $substr == substr($str, 0, strlen($substr));
        }
    }

    /**
     * Checks if a string ends with a given string.
     *
     * @param  string $str    A string
     * @param  string $substr A string to check against
     *
     * @return bool           True if str ends with substr
     */
    public static function endsWith($str, $substr)
    {
        if(is_string($str) && is_string($substr) && strlen($str) >= strlen($substr)) {
            return $substr == substr($str, strlen($str) - strlen($substr));
        }
    }
}