<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Tests;

use BeSimple\SoapCommon\Classmap;

/**
 * UnitTest for \BeSimple\SoapCommon\Classmap.
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ClassmapTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $classmap = new Classmap();

        $this->assertSame(array(), $classmap->all());
    }

    public function testAdd()
    {
        $classmap = new Classmap();

        $classmap->add('foobar', 'BeSimple\SoapCommon\Classmap');

        $this->expectException('InvalidArgumentException');

        $classmap->add('foobar', 'BeSimple\SoapCommon\Classmap');
    }

    public function testGet()
    {
        $classmap = new Classmap();

        $classmap->add('foobar', 'BeSimple\SoapCommon\Classmap');
        $this->assertSame('BeSimple\SoapCommon\Classmap', $classmap->get('foobar'));

        $this->expectException('InvalidArgumentException');

        $classmap->get('bar');
    }

    public function testSet()
    {
        $classmap = new Classmap();

        $classmap->add('foobar', 'BeSimple\SoapCommon\Tests\ClassmapTest');
        $classmap->add('foo', 'BeSimple\SoapCommon\Tests\Classmap');

        $map = array(
            'foobar' => 'BeSimple\SoapCommon\Classmap',
            'barfoo' => 'BeSimple\SoapCommon\Tests\ClassmapTest',
        );
        $classmap->set($map);

        $this->assertSame($map, $classmap->all());
    }

    public function testAddClassmap()
    {
        $classmap1 = new Classmap();
        $classmap2 = new Classmap();

        $classmap2->add('foobar', 'BeSimple\SoapCommon\Classmap');
        $classmap1->addClassmap($classmap2);

        $this->assertEquals(array('foobar' => 'BeSimple\SoapCommon\Classmap'), $classmap1->all());

        $this->expectException('InvalidArgumentException');

        $classmap1->addClassmap($classmap2);
    }
}
