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
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var int
     */
    private $lifetime;

    public function __construct()
    {
        $this->enabled = true;
        $this->directory = sys_get_temp_dir();
        $this->lifetime = 0;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
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

        return $this;
    }

    public function getLifetime()
    {
        return (int) $this->lifetime;
    }

    public function setLifetime($lifetime)
    {
        $this->lifetime = (int) $lifetime;

        return $this;
    }
}
