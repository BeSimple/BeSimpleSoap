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

use BeSimple\SoapCommon\WsSecurityKey;
use ass\XmlSecurity\Key as XmlSecurityKey;

class WsSecurityKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testHasKeys()
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(\ass\XmlSecurity\Key::RSA_SHA1, $filename);
        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientcert.pem';
        $wsk->addPublicKey(\ass\XmlSecurity\Key::RSA_SHA1, $filename);

        $this->assertTrue($wsk->hasKeys());
        $this->assertTrue($wsk->hasPrivateKey());
        $this->assertTrue($wsk->hasPublicKey());
    }

    public function testHasKeysNone()
    {
        $wsk = new WsSecurityKey();

        $this->assertFalse($wsk->hasKeys());
        $this->assertFalse($wsk->hasPrivateKey());
        $this->assertFalse($wsk->hasPublicKey());
    }

    public function testHasPrivateKey()
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(\ass\XmlSecurity\Key::RSA_SHA1, $filename);

        $this->assertFalse($wsk->hasKeys());
        $this->assertTrue($wsk->hasPrivateKey());
    }

    public function testHasPublicKey()
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientcert.pem';
        $wsk->addPublicKey(\ass\XmlSecurity\Key::RSA_SHA1, $filename);

        $this->assertFalse($wsk->hasKeys());
        $this->assertTrue($wsk->hasPublicKey());
    }

    public function testAddPrivateKey()
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(\ass\XmlSecurity\Key::RSA_SHA1, $filename);

        $this->assertTrue($wsk->hasPrivateKey());
        $this->assertInstanceOf('ass\XmlSecurity\Key', $wsk->getPrivateKey());
    }

    public function testAddPrivateKeySessionKey()
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(\ass\XmlSecurity\Key::TRIPLEDES_CBC);

        $this->assertTrue($wsk->hasPrivateKey());
        $this->assertInstanceOf('ass\XmlSecurity\Key', $wsk->getPrivateKey());
    }

    public function testAddPrivateKeyNoFile()
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientkey.pem';
        $wsk->addPrivateKey(\ass\XmlSecurity\Key::RSA_SHA1, file_get_contents($filename), false);

        $this->assertTrue($wsk->hasPrivateKey());
        $this->assertInstanceOf('ass\XmlSecurity\Key', $wsk->getPrivateKey());
    }

    public function testAddPublicKey()
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientcert.pem';
        $wsk->addPublicKey(\ass\XmlSecurity\Key::RSA_SHA1, $filename);

        $this->assertTrue($wsk->hasPublicKey());
        $this->assertInstanceOf('ass\XmlSecurity\Key', $wsk->getPublicKey());
    }

    public function testAddPublicKeyNoFile()
    {
        $wsk = new WsSecurityKey();

        $filename = __DIR__.DIRECTORY_SEPARATOR.'Fixtures/clientcert.pem';
        $wsk->addPublicKey(\ass\XmlSecurity\Key::RSA_SHA1, file_get_contents($filename), false);

        $this->assertTrue($wsk->hasPublicKey());
        $this->assertInstanceOf('ass\XmlSecurity\Key', $wsk->getPublicKey());
    }
}
