<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Util;

/**
 * OutputBuffer provides utility methods to work with PHP's output buffer.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class OutputBuffer
{
    /**
     * Gets the output created by the given callable.
     *
     * @param callable $callback A callable to execute
     *
     * @return string The output
     */
    static public function get($callback)
    {
        ob_start();
        $callback();
        return ob_get_clean();
    }
}