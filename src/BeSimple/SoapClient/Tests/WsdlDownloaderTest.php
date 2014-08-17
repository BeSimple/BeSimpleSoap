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
     * @dataProvider provideDownloadLocalFile
     */
    public function testDownloadWithLocalFile($source, $regexp, $nbDownloads)
    {
        $this->testDownload($source, $regexp, $nbDownloads);
    }

    /**
     * @dataProvider provideDownloadRemoteFile
     */
    public function testDownloadWithRemoteFile($source, $regexp, $nbDownloads)
    {
        $this->skipIfNotPhp54();

        $this->testDownload($source, $regexp, $nbDownloads);
    }

    public function provideDownloadLocalFile()
    {
        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/xsdinclude/xsdinctest_relative.xml',
                '\.\./type_include\.xsd',
                1,
            ),
        );
    }

    public function provideDownloadRemoteFile()
    {
        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/build_include/xsdinctest_absolute.xml',
                '%s/wsdl_[a-f0-9]{32}\.cache',
                2,
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

    protected function testDownload($source, $regexp, $nbDownloads)
    {
        $cacheDirectory = vfsStream::setup('wsdl');
        $cacheUrl = vfsStream::url('wsdl');

        $cache = new Cache();
        $cache->setEnabled(true);
        $cache->setDirectory($cacheUrl);

        $wsdlDownloader = new WsdlDownloader(new Curl(array(
            'proxy_host' => false,
        )), true, $cache);

        $this->assertCount(0, $cacheDirectory->getChildren());
        $cacheFilePath = $wsdlDownloader->download($source);
        $this->assertCount($nbDownloads, $cacheDirectory->getChildren());

        $this->assertRegExp('#'.sprintf($regexp, preg_quote($cacheUrl, '#')).'#', file_get_contents($cacheFilePath));
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
        $this->assertFalse($m->invoke($wsdlDownloader, 'c:\dir\test.html'));
        $this->assertFalse($m->invoke($wsdlDownloader, 'file:///c:/dir/test.html'));
        $this->assertFalse($m->invoke($wsdlDownloader, '/dir/test.html'));
        $this->assertFalse($m->invoke($wsdlDownloader, '../dir/test.html'));
    }

    /**
     * @dataProvider provideResolveWsdlIncludesLocalFile
     */
    public function testResolveWsdlIncludesWithLocalFile($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads)
    {
        $this->testResolveWsdlIncludes($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads);
    }

    /**
     * @dataProvider provideResolveWsdlIncludesRemoteFile
     */
    public function testResolveWsdlIncludesWithRemoteFile($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads)
    {
        $this->skipIfNotPhp54();

        $this->testResolveWsdlIncludes($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads);
    }

    public function provideResolveWsdlIncludesLocalFile()
    {
        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/wsdlinclude/wsdlinctest_relative.xml',
                '%s/cache_local_relative.xml',
                null,
                '\.\./wsdl_include\.wsdl',
                1,
            ),
        );
    }

    public function provideResolveWsdlIncludesRemoteFile()
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

    protected function testResolveWsdlIncludes($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads)
    {
        $cacheDirectory = vfsStream::setup('wsdl');
        $cacheUrl = vfsStream::url('wsdl');

        $cache = new Cache();
        $cache->setEnabled(true);
        $cache->setDirectory($cacheUrl);

        $wsdlDownloader = new WsdlDownloader(new Curl(array(
            'proxy_host' => false,
        )), true, $cache);
        $r = new \ReflectionClass($wsdlDownloader);
        $m = $r->getMethod('resolveRemoteIncludes');
        $m->setAccessible(true);

        $this->assertCount(0, $cacheDirectory->getChildren());

        $cacheFile = sprintf($cacheFile, $cacheUrl);
        $m->invoke($wsdlDownloader, file_get_contents($source), $cacheFile, $remoteParentUrl);
        $this->assertCount($nbDownloads, $cacheDirectory->getChildren());

        $this->assertRegExp('#'.sprintf($regexp, preg_quote($cacheUrl, '#')).'#', file_get_contents($cacheFile));
    }

    /**
     * @dataProvider provideResolveXsdIncludesLocalFile
     */
    public function testResolveXsdIncludesWithLocalFile($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads)
    {
        $this->testResolveXsdIncludes($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads);
    }

    /**
     * @dataProvider provideResolveXsdIncludesRemoteFile
     */
    public function testResolveXsdIncludesWithRemoteFile($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads)
    {
        $this->skipIfNotPhp54();

        $this->testResolveXsdIncludes($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads);
    }

    public function provideResolveXsdIncludesLocalFile()
    {
        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/xsdinclude/xsdinctest_relative.xml',
                '%s/cache_local_relative.xml',
                null,
                '\.\./type_include\.xsd',
                1,
            ),
        );
    }

    public function provideResolveXsdIncludesRemoteFile()
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

    protected function testResolveXsdIncludes($source, $cacheFile, $remoteParentUrl, $regexp, $nbDownloads)
    {
        $cacheDirectory = vfsStream::setup('wsdl');
        $cacheUrl = vfsStream::url('wsdl');

        $cache = new Cache();
        $cache->setEnabled(true);
        $cache->setDirectory($cacheUrl);

        $wsdlDownloader = new WsdlDownloader(new Curl(array(
            'proxy_host' => false,
        )), true, $cache);
        $r = new \ReflectionClass($wsdlDownloader);
        $m = $r->getMethod('resolveRemoteIncludes');
        $m->setAccessible(true);

        $this->assertCount(0, $cacheDirectory->getChildren());

        $cacheFile = sprintf($cacheFile, $cacheUrl);
        $m->invoke($wsdlDownloader, file_get_contents($source), $cacheFile, $remoteParentUrl);
        $this->assertCount($nbDownloads, $cacheDirectory->getChildren());

        $this->assertRegExp('#'.sprintf($regexp, preg_quote($cacheUrl, '#')).'#', file_get_contents($cacheFile));
    }

    public function testResolveRelativePathInUrl()
    {
        $wsdlDownloader = new WsdlDownloader(new Curl(array(
            'proxy_host' => false,
        )));

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
