<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle;

use BeSimple\SoapCommon\Cache as BaseCache;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Cache
{
    public function __construct($cacheDisabled, $type, $directory, $lifetime = null, $limit = null)
    {
        $isEnabled = (Boolean) $cacheDisabled ? BaseCache::DISABLED : BaseCache::ENABLED;

        BaseCache::setEnabled($isEnabled);

        BaseCache::setType($type);
        BaseCache::setDirectory($directory);

        if (null !== $lifetime) {
            BaseCache::setLifetime($lifetime);
        }

        if (null !== $limit) {
            BaseCache::setLimit($limit);
        }
    }
}
