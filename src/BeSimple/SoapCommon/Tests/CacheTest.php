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

namespace BeSimple\SoapCommon\Tests;

use BeSimple\SoapCommon\Cache;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testEnabled()
    {
        $cache = $this->getCache();
        $this->assertTrue($cache->isEnabled());

        $this->assertInstanceOf('BeSimple\SoapCommon\Cache', $cache->setEnabled(false));
        $this->assertFalse($cache->isEnabled());

        $cache->setEnabled(true);
        $this->assertTrue($cache->isEnabled());
    }

    public function testSetDirectory()
    {
        $cache = $this->getCache();
        $this->assertSame(sys_get_temp_dir(), $cache->getDirectory());

        vfsStream::setup('Fixtures');

        $dir = vfsStream::url('Fixtures/foo');
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('foo'));
        $cache->setDirectory($dir);
        $this->assertEquals($dir, $cache->getDirectory());
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('foo'));

        $dir = vfsStream::url('Fixtures/bar');
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('bar'));
        $cache->setDirectory($dir);
        $this->assertEquals($dir, $cache->getDirectory());
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('bar'));
    }

    public function testLifetime()
    {
        $cache = $this->getCache();
        $this->assertSame(0, $cache->getLifetime());

        $this->assertInstanceOf('BeSimple\SoapCommon\Cache', $cache->setLifetime(86400)); // 1 day
        $this->assertSame(86400, $cache->getLifetime());
    }

    private function getCache()
    {
        return new Cache();
    }
}
