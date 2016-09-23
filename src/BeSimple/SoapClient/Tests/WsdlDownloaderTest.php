<?php

/*
 * This file is part of the BeSimpleSoapClient.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapClient\Tests;

use BeSimple\SoapClient\WsdlDownloader;
use BeSimple\SoapCommon\Cache;
use BeSimple\SoapClient\Curl;
use Symfony\Component\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * @author Andreas Schamberger <mail@andreass.net>
 * @author Francis Besset <francis.bessset@gmail.com>
 */
class WsdlDownloaderTest extends AbstractWebserverTest
{
    static protected $filesystem;

    static protected $fixturesPath;

    /**
     * @dataProvider provideDownload
     */
    public function testDownload($source, $regexp, $nbDownloads)
    {
        $wsdlCacheDir = vfsStream::setup('wsdl');
        $wsdlCacheUrl = $wsdlCacheDir->url('wsdl');

        Cache::setEnabled(Cache::ENABLED);
        Cache::setDirectory($wsdlCacheUrl);
        $cacheDirForRegExp = preg_quote($wsdlCacheUrl, '#');

        $wsdlDownloader = new WsdlDownloader(new Curl(array(
            'proxy_host' => false,
        )));
        $this->assertCount(0, $wsdlCacheDir->getChildren());

        $cacheFileName = $wsdlDownloader->download($source);
        $this->assertCount($nbDownloads, $wsdlCacheDir->getChildren());

        $this->assertRegExp('#'.sprintf($regexp, $cacheDirForRegExp).'#', file_get_contents($cacheFileName));
    }

    public function provideDownload()
    {
        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/build_include/xsdinctest_absolute.xml',
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2,
            ),
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/xsdinclude/xsdinctest_relative.xml',
                '\.\./type_include\.xsd',
                1,
            ),
            array(
                sprintf('http://localhost:%d/build_include/xsdinctest_absolute.xml', WEBSERVER_PORT),
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2,
            ),
            array(
                sprintf('http://localhost:%d/xsdinclude/xsdinctest_relative.xml', WEBSERVER_PORT),
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2,
            ),
        );
    }

    public function testIsRemoteFile()
    {
        $wsdlDownloader = new WsdlDownloader(new Curl());

        $r = new \ReflectionClass($wsdlDownloader);
        $m = $r->getMethod('isRemoteFile');
        $m->setAccessible(true);

        $this->assertTrue($m->invoke($wsdlDownloader, 'http://www.php.net/'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'http://localhost/'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'http://mylocaldomain/'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'http://www.php.net/dir/test.html'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'http://localhost/dir/test.html'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'http://mylocaldomain/dir/test.html'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'https://www.php.net/'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'https://localhost/'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'https://mylocaldomain/'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'https://www.php.net/dir/test.html'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'https://localhost/dir/test.html'));
        $this->assertTrue($m->invoke($wsdlDownloader, 'https://mylocaldomain/dir/test.html'));
        $this->assertFalse($m->invoke($wsdlDownloader, 'c:/dir/test.html'));
        $this->assertFalse($m->invoke($wsdlDownloader, '/dir/test.html'));
        $this->assertFalse($m->invoke($wsdlDownloader, '../dir/test.html'));
    }

    /**
     * @dataProvider provideResolveWsdlIncludes
     */
    public function testResolveWsdlIncludes($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads)
    {
        $wsdlCacheDir = vfsStream::setup('wsdl');
        $wsdlCacheUrl = $wsdlCacheDir->url('wsdl');

        Cache::setEnabled(Cache::ENABLED);
        Cache::setDirectory($wsdlCacheUrl);
        $cacheDirForRegExp = preg_quote($wsdlCacheUrl, '#');

        $wsdlDownloader = new WsdlDownloader(new Curl(array(
            'proxy_host' => false,
        )));
        $r = new \ReflectionClass($wsdlDownloader);
        $m = $r->getMethod('resolveRemoteIncludes');
        $m->setAccessible(true);

        $this->assertCount(0, $wsdlCacheDir->getChildren());

        $cacheFile = sprintf($cacheFile, $wsdlCacheUrl);
        $m->invoke($wsdlDownloader, file_get_contents($source), $cacheFile, $remoteParentUrl);
        $this->assertCount($nbDownloads, $wsdlCacheDir->getChildren());

        $this->assertRegExp('#'.sprintf($regexp, $cacheDirForRegExp).'#', file_get_contents($cacheFile));
    }

    public function provideResolveWsdlIncludes()
    {
        $remoteUrlAbsolute = sprintf('http://localhost:%d/build_include/wsdlinctest_absolute.xml', WEBSERVER_PORT);
        $remoteUrlRelative = sprintf('http://localhost:%d/wsdlinclude/wsdlinctest_relative.xml', WEBSERVER_PORT);

        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/build_include/wsdlinctest_absolute.xml',
                '%s/cache_local_absolute.xml',
                null,
                '%s/wsdl_[a-f0-9]{32}.cache',
                2,
            ),
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/wsdlinclude/wsdlinctest_relative.xml',
                '%s/cache_local_relative.xml',
                null,
                '\.\./wsdl_include\.wsdl',
                1,
            ),
            array(
                $remoteUrlAbsolute,
                '%s/cache_remote_absolute.xml',
                $remoteUrlAbsolute,
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2,
            ),
            array(
                $remoteUrlRelative,
                '%s/cache_remote_relative.xml',
                $remoteUrlRelative,
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2
            ),
        );
    }

    /**
     * @dataProvider provideResolveXsdIncludes
     */
    public function testResolveXsdIncludes($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads)
    {
        $wsdlCacheDir = vfsStream::setup('wsdl');
        $wsdlCacheUrl = $wsdlCacheDir->url('wsdl');

        Cache::setEnabled(Cache::ENABLED);
        Cache::setDirectory($wsdlCacheUrl);
        $cacheDirForRegExp = preg_quote($wsdlCacheUrl, '#');

        $wsdlDownloader = new WsdlDownloader(new Curl(array(
            'proxy_host' => false,
        )));
        $r = new \ReflectionClass($wsdlDownloader);
        $m = $r->getMethod('resolveRemoteIncludes');
        $m->setAccessible(true);

        $this->assertCount(0, $wsdlCacheDir->getChildren());

        $cacheFile = sprintf($cacheFile, $wsdlCacheUrl);
        $m->invoke($wsdlDownloader, file_get_contents($source), $cacheFile, $remoteParentUrl);
        $this->assertCount($nbDownloads, $wsdlCacheDir->getChildren());

        $this->assertRegExp('#'.sprintf($regexp, $cacheDirForRegExp).'#', file_get_contents($cacheFile));
    }

    public function provideResolveXsdIncludes()
    {
        $remoteUrlAbsolute = sprintf('http://localhost:%d/build_include/xsdinctest_absolute.xml', WEBSERVER_PORT);
        $remoteUrlRelative = sprintf('http://localhost:%d/xsdinclude/xsdinctest_relative.xml', WEBSERVER_PORT);

        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/build_include/xsdinctest_absolute.xml',
                '%s/cache_local_absolute.xml',
                null,
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2,
            ),
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/xsdinclude/xsdinctest_relative.xml',
                '%s/cache_local_relative.xml',
                null,
                '\.\./type_include\.xsd',
                1,
            ),
            array(
                $remoteUrlAbsolute,
                '%s/cache_remote_absolute.xml',
                $remoteUrlAbsolute,
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2,
            ),
            array(
                $remoteUrlRelative,
                '%s/cache_remote_relative.xml',
                $remoteUrlRelative,
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2,
            ),
        );
    }

    public function testResolveRelativePathInUrl()
    {
        $wsdlDownloader = new WsdlDownloader(new Curl());

        $r = new \ReflectionClass($wsdlDownloader);
        $m = $r->getMethod('resolveRelativePathInUrl');
        $m->setAccessible(true);

        $this->assertEquals('http://localhost:8080/test', $m->invoke($wsdlDownloader, 'http://localhost:8080/sub', '/test'));
        $this->assertEquals('http://localhost:8080/test', $m->invoke($wsdlDownloader, 'http://localhost:8080/sub/', '/test'));

        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost/sub', '/test'));
        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/', '/test'));

        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost', './test'));
        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost/', './test'));

        $this->assertEquals('http://localhost/sub/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub', './test'));
        $this->assertEquals('http://localhost/sub/sub/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub/', './test'));

        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub', '../test'));
        $this->assertEquals('http://localhost/sub/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub/', '../test'));

        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub/sub', '../../test'));
        $this->assertEquals('http://localhost/sub/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub/sub/', '../../test'));

        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub/sub/sub', '../../../test'));
        $this->assertEquals('http://localhost/sub/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub/sub/sub/', '../../../test'));

        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub/sub', '../../../test'));
        $this->assertEquals('http://localhost/test', $m->invoke($wsdlDownloader, 'http://localhost/sub/sub/sub/', '../../../test'));
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$filesystem  = new Filesystem();
        self::$fixturesPath = __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR;
        self::$filesystem->mkdir(self::$fixturesPath.'build_include');

        foreach (array('wsdlinclude/wsdlinctest_absolute.xml', 'xsdinclude/xsdinctest_absolute.xml') as $file) {
            $content = file_get_contents(self::$fixturesPath.$file);
            $content = preg_replace('#'.preg_quote('%location%').'#', sprintf('localhost:%d', WEBSERVER_PORT), $content);

            self::$filesystem->dumpFile(self::$fixturesPath.'build_include'.DIRECTORY_SEPARATOR.pathinfo($file, PATHINFO_BASENAME), $content);
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$filesystem->remove(self::$fixturesPath.'build_include');
    }
}
