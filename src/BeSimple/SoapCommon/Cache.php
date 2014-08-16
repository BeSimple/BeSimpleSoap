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
final class Cache
{
    const TYPE_NONE   = 0;
    const TYPE_DISK   = 1;
    const TYPE_MEMORY = 2;

    static protected $types = array(
        self::TYPE_NONE,
        self::TYPE_DISK,
        self::TYPE_MEMORY,
    );

    protected $type;

    protected $directory;

    protected $lifetime;

    static public function getTypes()
    {
        return self::$types;
    }

    public function isEnabled()
    {
        return self::TYPE_NONE !== $this->type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        if (!in_array($type, self::getTypes(), true)) {
            throw new \InvalidArgumentException('The cache type has to be either TYPE_NONE, TYPE_DISK or TYPE_MEMORY');
        }

        $this->type = $type;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function setDirectory($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $this->directory = $directory;
    }

    public function getLifetime()
    {
        $this->lifetime = $lifetime;
    }

    public function setLifetime($lifetime)
    {
        return (int) $this->lifetime;
    }
}
