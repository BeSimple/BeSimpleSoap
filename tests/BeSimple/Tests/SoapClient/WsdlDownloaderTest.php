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

namespace BeSimple\SoapClient;

use BeSimple\SoapClient\WsdlDownloader;
use BeSimple\SoapClient\Curl;

/**
 * @author Andreas Schamberger <mail@andreass.net>
 */
class WsdlDownloaderTest extends \PHPUnit_Framework_TestCase
{
    protected $webserverProcessId;

    protected function startPhpWebserver()
    {
        if ('Windows' == substr(php_uname('s'), 0, 7 )) {
            $powershellCommand = "\$app = start-process php.exe -ArgumentList '-S localhost:8000 -t ".__DIR__.DIRECTORY_SEPARATOR."Fixtures' -WindowStyle 'Hidden' -passthru; Echo \$app.Id;";
            $shellCommand = 'powershell -command "& {'.$powershellCommand.'}"';
        } else {
            $shellCommand = "nohup php -S localhost:8000 -t ".__DIR__.DIRECTORY_SEPARATOR."Fixtures &";
        }
        $output = array();
        exec($shellCommand, $output);
        $this->webserverProcessId = $output[0]; // pid is in first element
    }

    protected function stopPhpWebserver()
    {
        if (!is_null($this->webserverProcessId)) {
            if ('Windows' == substr(php_uname('s'), 0, 7 )) {
                exec('TASKKILL /F /PID ' . $this->webserverProcessId);
            } else {
                exec('kill ' . $this->webserverProcessId);
            }
            $this->webserverProcessId = null;
        }
    }

    public function testDownload()
    {
        $this->startPhpWebserver();

        $curl = new Curl();
        $wd = new WsdlDownloader($curl);

        $cacheDir = ini_get('soap.wsdl_cache_dir');
        if (!is_dir($cacheDir)) {
            $cacheDir = sys_get_temp_dir();
            $cacheDirForRegExp = preg_quote( $cacheDir );
        }

        $tests = array(
            'localWithAbsolutePath' => array(
                 'source' => __DIR__.DIRECTORY_SEPARATOR.'Fixtures/xsdinclude/xsdinctest_absolute.xml',
                 'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
            'localWithRelativePath' => array(
                 'source' => __DIR__.DIRECTORY_SEPARATOR.'Fixtures/xsdinclude/xsdinctest_relative.xml',
                 'assertRegExp' => '~.*\.\./type_include\.xsd.*~',
            ),
            'remoteWithAbsolutePath' => array(
                 'source' => 'http://localhost:8000/xsdinclude/xsdinctest_absolute.xml',
                 'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
            'remoteWithAbsolutePath' => array(
                 'source' => 'http://localhost:8000/xsdinclude/xsdinctest_relative.xml',
                 'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
        );

        foreach ($tests as $name => $values) {
            $cacheFileName = $wd->download($values['source']);
            $result = file_get_contents($cacheFileName);
            $this->assertRegExp($values['assertRegExp'],$result,$name);
            unlink($cacheFileName);
        }

        $this->stopPhpWebserver();
    }

    public function testIsRemoteFile()
    {
        $curl = new Curl();
        $wd = new WsdlDownloader($curl);

        $class = new \ReflectionClass($wd);
        $method = $class->getMethod('isRemoteFile');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($wd, 'http://www.php.net/'));
        $this->assertTrue($method->invoke($wd, 'http://localhost/'));
        $this->assertTrue($method->invoke($wd, 'http://mylocaldomain/'));
        $this->assertTrue($method->invoke($wd, 'http://www.php.net/dir/test.html'));
        $this->assertTrue($method->invoke($wd, 'http://localhost/dir/test.html'));
        $this->assertTrue($method->invoke($wd, 'http://mylocaldomain/dir/test.html'));
        $this->assertTrue($method->invoke($wd, 'https://www.php.net/'));
        $this->assertTrue($method->invoke($wd, 'https://localhost/'));
        $this->assertTrue($method->invoke($wd, 'https://mylocaldomain/'));
        $this->assertTrue($method->invoke($wd, 'https://www.php.net/dir/test.html'));
        $this->assertTrue($method->invoke($wd, 'https://localhost/dir/test.html'));
        $this->assertTrue($method->invoke($wd, 'https://mylocaldomain/dir/test.html'));
        $this->assertFalse($method->invoke($wd, 'c:/dir/test.html'));
        $this->assertFalse($method->invoke($wd, '/dir/test.html'));
        $this->assertFalse($method->invoke($wd, '../dir/test.html'));
    }

    public function testResolveWsdlIncludes()
    {
        $this->startPhpWebserver();

        $curl = new Curl();
        $wd = new WsdlDownloader($curl);

        $class = new \ReflectionClass($wd);
        $method = $class->getMethod('resolveRemoteIncludes');
        $method->setAccessible(true);

        $cacheDir = ini_get('soap.wsdl_cache_dir');
        if (!is_dir($cacheDir)) {
            $cacheDir = sys_get_temp_dir();
            $cacheDirForRegExp = preg_quote( $cacheDir );
        }

        $remoteUrlAbsolute = 'http://localhost:8000/wsdlinclude/wsdlinctest_absolute.xml';
        $remoteUrlRelative = 'http://localhost:8000/wsdlinclude/wsdlinctest_relative.xml';
        $tests = array(
            'localWithAbsolutePath' => array(
                     'source' => __DIR__.DIRECTORY_SEPARATOR.'Fixtures/wsdlinclude/wsdlinctest_absolute.xml',
                     'cacheFile' => $cacheDir.'/cache_local_absolute.xml',
                     'remoteParentUrl' => null,
                     'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
            'localWithRelativePath' => array(
                     'source' => __DIR__.DIRECTORY_SEPARATOR.'Fixtures/wsdlinclude/wsdlinctest_relative.xml',
                     'cacheFile' => $cacheDir.'/cache_local_relative.xml',
                     'remoteParentUrl' => null,
                     'assertRegExp' => '~.*\.\./wsdl_include\.wsdl.*~',
            ),
            'remoteWithAbsolutePath' => array(
                     'source' => $remoteUrlAbsolute,
                     'cacheFile' => $cacheDir.'/cache_remote_absolute.xml',
                     'remoteParentUrl' => $remoteUrlAbsolute,
                     'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
            'remoteWithAbsolutePath' => array(
                     'source' => $remoteUrlRelative,
                     'cacheFile' => $cacheDir.'/cache_remote_relative.xml',
                     'remoteParentUrl' => $remoteUrlRelative,
                     'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
        );

        foreach ($tests as $name => $values) {
            $wsdl = file_get_contents( $values['source'] );
            $method->invoke($wd, $wsdl, $values['cacheFile'],$values['remoteParentUrl']);
            $result = file_get_contents($values['cacheFile']);
            $this->assertRegExp($values['assertRegExp'],$result,$name);
            unlink($values['cacheFile']);
        }

        $this->stopPhpWebserver();
    }

    public function testResolveXsdIncludes()
    {
        $this->startPhpWebserver();

        $curl = new Curl();
        $wd = new WsdlDownloader($curl);

        $class = new \ReflectionClass($wd);
        $method = $class->getMethod('resolveRemoteIncludes');
        $method->setAccessible(true);

        $cacheDir = ini_get('soap.wsdl_cache_dir');
        if (!is_dir($cacheDir)) {
            $cacheDir = sys_get_temp_dir();
            $cacheDirForRegExp = preg_quote( $cacheDir );
        }

        $remoteUrlAbsolute = 'http://localhost:8000/xsdinclude/xsdinctest_absolute.xml';
        $remoteUrlRelative = 'http://localhost:8000/xsdinclude/xsdinctest_relative.xml';
        $tests = array(
            'localWithAbsolutePath' => array(
                 'source' => __DIR__.DIRECTORY_SEPARATOR.'Fixtures/xsdinclude/xsdinctest_absolute.xml',
                 'cacheFile' => $cacheDir.'/cache_local_absolute.xml',
                 'remoteParentUrl' => null,
                 'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
            'localWithRelativePath' => array(
                 'source' => __DIR__.DIRECTORY_SEPARATOR.'Fixtures/xsdinclude/xsdinctest_relative.xml',
                 'cacheFile' => $cacheDir.'/cache_local_relative.xml',
                 'remoteParentUrl' => null,
                 'assertRegExp' => '~.*\.\./type_include\.xsd.*~',
            ),
            'remoteWithAbsolutePath' => array(
                 'source' => $remoteUrlAbsolute,
                 'cacheFile' => $cacheDir.'/cache_remote_absolute.xml',
                 'remoteParentUrl' => $remoteUrlAbsolute,
                 'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
            'remoteWithAbsolutePath' => array(
                 'source' => $remoteUrlRelative,
                 'cacheFile' => $cacheDir.'/cache_remote_relative.xml',
                 'remoteParentUrl' => $remoteUrlRelative,
                 'assertRegExp' => '~.*'.$cacheDirForRegExp.'\\\wsdl_.*\.cache.*~',
            ),
        );

        foreach ($tests as $name => $values) {
            $wsdl = file_get_contents( $values['source'] );
            $method->invoke($wd, $wsdl, $values['cacheFile'],$values['remoteParentUrl']);
            $result = file_get_contents($values['cacheFile']);
            $this->assertRegExp($values['assertRegExp'],$result,$name);
            unlink($values['cacheFile']);
        }

        $this->stopPhpWebserver();
    }

    public function testResolveRelativePathInUrl()
    {
        $curl = new Curl();
        $wd = new WsdlDownloader($curl);

        $class = new \ReflectionClass($wd);
        $method = $class->getMethod('resolveRelativePathInUrl');
        $method->setAccessible(true);

        $this->assertEquals('http://localhost:8080/test', $method->invoke($wd, 'http://localhost:8080/sub', '/test'));
        $this->assertEquals('http://localhost:8080/test', $method->invoke($wd, 'http://localhost:8080/sub/', '/test'));

        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost/sub', '/test'));
        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost/sub/', '/test'));

        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost', './test'));
        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost/', './test'));

        $this->assertEquals('http://localhost/sub/test', $method->invoke($wd, 'http://localhost/sub/sub', './test'));
        $this->assertEquals('http://localhost/sub/sub/test', $method->invoke($wd, 'http://localhost/sub/sub/', './test'));

        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost/sub/sub', '../test'));
        $this->assertEquals('http://localhost/sub/test', $method->invoke($wd, 'http://localhost/sub/sub/', '../test'));

        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost/sub/sub/sub', '../../test'));
        $this->assertEquals('http://localhost/sub/test', $method->invoke($wd, 'http://localhost/sub/sub/sub/', '../../test'));

        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost/sub/sub/sub/sub', '../../../test'));
        $this->assertEquals('http://localhost/sub/test', $method->invoke($wd, 'http://localhost/sub/sub/sub/sub/', '../../../test'));

        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost/sub/sub/sub', '../../../test'));
        $this->assertEquals('http://localhost/test', $method->invoke($wd, 'http://localhost/sub/sub/sub/', '../../../test'));
    }
}