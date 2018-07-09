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

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author francis.besset@gmail.com <francis.besset@gmail.com>
 */
abstract class AbstractWebServerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProcessBuilder
     */
    static protected $webserver;
    static protected $websererPortLength;

    public static function setUpBeforeClass()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            self::markTestSkipped('PHP Webserver is available from PHP 5.4');
        }

        $phpFinder = new PhpExecutableFinder();
        self::$webserver = ProcessBuilder::create(array(
            'exec', // used exec binary (https://github.com/symfony/symfony/issues/5759)
            $phpFinder->find(),
            '-S',
            sprintf('localhost:%d', WEBSERVER_PORT),
            '-t',
            __DIR__.DIRECTORY_SEPARATOR.'Fixtures',
        ))->getProcess();

        self::$webserver->start();
        usleep(100000);

        self::$websererPortLength = strlen(WEBSERVER_PORT);
    }

    public static function tearDownAfterClass()
    {
        self::$webserver->stop(0);
        usleep(100000);
    }
}
