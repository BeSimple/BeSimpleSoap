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
    // when using the SetUpTearDownTrait, methods like doSetup() can
    // be defined with and without the 'void' return type, as you wish
    use \Symfony\Bridge\PhpUnit\SetUpTearDownTrait;

    protected static $filesystem;

    protected static $fixturesPath;

    /**
     * @dataProvider provideDownload
     */
    public function testDownloadDownloadsToVfs($source, $regexp, $nbDownloads)
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

        $this->assertIsReadable($cacheFileName);

        //Test that the Cache filename is valid
        $regexp = '#'.sprintf($regexp, $cacheDirForRegExp).'#';
        $this->assertRegExp($regexp, $cacheFileName);

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
                '%s/wsdl_[a-f0-9]{32}\.cache',
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
        $wsdlIncludeMd5 = md5('http://' . sprintf('localhost:%d', WEBSERVER_PORT) . '/wsdl_include.wsdl');
        $expectedWsdl = 'wsdl_'.$wsdlIncludeMd5.'.cache';

        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/build_include/wsdlinctest_absolute.xml',
                '%s/cache_local_absolute.xml',
                null,
                $expectedWsdl,
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
                $expectedWsdl,
                2,
            ),
            array(
                $remoteUrlRelative,
                '%s/cache_remote_relative.xml',
                $remoteUrlRelative,
                $expectedWsdl,
                2,
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
        $xsdIncludeMd5 = md5('http://' . sprintf('localhost:%d', WEBSERVER_PORT) . '/type_include.xsd');
        $expectedXsd = 'wsdl_'.$xsdIncludeMd5.'.cache';

        return array(
            array(
                __DIR__.DIRECTORY_SEPARATOR.'Fixtures/build_include/xsdinctest_absolute.xml',
                '%s/cache_local_absolute.xml',
                null,
                $expectedXsd,
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
                $expectedXsd,
                2,
            ),
            array(
                $remoteUrlRelative,
                '%s/cache_remote_relative.xml',
                $remoteUrlRelative,
                $expectedXsd,
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

    /**
     * Test that non HTTP 200 responses throw an exception
     *
     * @dataProvider invalidResponseCodesDataProvider
     *
     * @throws \ErrorException
     */
    public function testInvalidResponseCodes($responseCode)
    {
        $this->expectException('ErrorException');
        $this->expectExceptionMessage('SOAP-ERROR: Parsing WSDL: Unexpected response code received from \'http://somefake.url/wsdl\', response code: ' . $responseCode);

        $curlMock = $this->createMock('BeSimple\SoapClient\Curl');
        $curlMock->expects($this->any())
            ->method('getResponseStatusCode')
            ->willReturn($responseCode);

        $wsdlDownloader = new WsdlDownloader($curlMock);

        $wsdlDownloader->download('http://somefake.url/wsdl');
    }

    public function invalidResponseCodesDataProvider()
    {
        return [
            'No Content' => [204],
            'Moved Permanently' => [301],
            'Found' => [302],
            'Unathorized' => [401],
            'Not Found' => [404],
            'Internal Server Error' => [500]
        ];
    }

    /**
     * Test that HTTP 200 responses downloads and stores the WSDL correctly
     */
    public function testValidResponseCode()
    {
        $curlMock = $this->createMock('BeSimple\SoapClient\Curl');
        $curlMock->expects($this->any())
            ->method('getResponseStatusCode')
            ->willReturn(200);
        $curlMock->expects($this->once())
            ->method('getResponseBody')
            ->willReturn('<?xml version="1.0"?><wsdl:types xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:xs="http://www.w3.org/2001/XMLSchema"></wsdl:types>');

        $wsdlDownloader = new WsdlDownloader($curlMock);

        $result = $wsdlDownloader->download('http://somefake.url/wsdl');

        $this->assertRegExp('/.*wsdl_[a-f0-9]{32}\.cache/', $result);
    }

    public static function doSetUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$filesystem = new Filesystem();
        self::$fixturesPath = __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR;
        self::$filesystem->mkdir(self::$fixturesPath.'build_include');

        foreach (array('wsdlinclude/wsdlinctest_absolute.xml', 'xsdinclude/xsdinctest_absolute.xml') as $file) {
            $content = file_get_contents(self::$fixturesPath.$file);
            $content = preg_replace('#'.preg_quote('%location%').'#', sprintf('localhost:%d', WEBSERVER_PORT), $content);

            self::$filesystem->dumpFile(self::$fixturesPath.'build_include'.DIRECTORY_SEPARATOR.pathinfo($file, PATHINFO_BASENAME), $content);
        }
    }

    public static function doTearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$filesystem->remove(self::$fixturesPath.'build_include');
    }
}
