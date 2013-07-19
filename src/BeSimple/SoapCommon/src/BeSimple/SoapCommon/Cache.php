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

namespace BeSimple\SoapCommon;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Cache
{
    const DISABLED = 0;
    const ENABLED  = 1;

    const TYPE_NONE        = WSDL_CACHE_NONE;
    const TYPE_DISK        = WSDL_CACHE_DISK;
    const TYPE_MEMORY      = WSDL_CACHE_MEMORY;
    const TYPE_DISK_MEMORY = WSDL_CACHE_BOTH;

    static protected $types = array(
        self::TYPE_NONE,
        self::TYPE_DISK,
        self::TYPE_MEMORY,
        self::TYPE_DISK_MEMORY,
    );

    static public function getTypes()
    {
        return self::$types;
    }

    static public function isEnabled()
    {
        return self::iniGet('soap.wsdl_cache_enabled');
    }

    static public function setEnabled($enabled)
    {
        if (!in_array($enabled, array(self::ENABLED, self::DISABLED), true)) {
            throw new \InvalidArgumentException();
        }

        self::iniSet('soap.wsdl_cache_enabled', $enabled);
    }

    static public function getType()
    {
        return self::iniGet('soap.wsdl_cache');
    }

    static public function setType($type)
    {
        if (!in_array($type, self::getTypes(), true)) {
            throw new \InvalidArgumentException('The cache type has to be either Cache::TYPE_NONE, Cache::TYPE_DISK, Cache::TYPE_MEMORY or Cache::TYPE_DISK_MEMORY');
        }

        self::iniSet('soap.wsdl_cache', $type);
    }

    static public function getDirectory()
    {
        return self::iniGet('soap.wsdl_cache_dir');
    }

    static public function setDirectory($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        self::iniSet('soap.wsdl_cache_dir', $directory);
    }

    static public function getLifetime()
    {
        return self::iniGet('soap.wsdl_cache_ttl');
    }

    static public function setLifetime($lifetime)
    {
        self::iniSet('soap.wsdl_cache_ttl', $lifetime);
    }

    static public function getLimit()
    {
        return self::iniGet('soap.wsdl_cache_limit');
    }

    static public function setLimit($limit)
    {
        self::iniSet('soap.wsdl_cache_limit', $limit);
    }

    static protected function iniGet($key)
    {
        return ini_get($key);
    }

    static protected function iniSet($key, $value)
    {
        ini_set($key, $value);
    }
}