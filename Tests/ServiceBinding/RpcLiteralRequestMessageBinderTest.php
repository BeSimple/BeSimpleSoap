<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Tests\ServiceBinding;

use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapBundle\ServiceDefinition\Argument;
use BeSimple\SoapBundle\ServiceDefinition\Type;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\PropertyComplexType;
use BeSimple\SoapBundle\ServiceBinding\RpcLiteralRequestMessageBinder;
use BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Foo;
use BeSimple\SoapBundle\Util\Collection;

/**
 * UnitTest for \BeSimple\SoapBundle\ServiceBinding\RpcLiteralRequestMessageBinder.
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class RpcLiteralRequestMessageBinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider messageProvider
     */
    public function testProcessMessage(Method $method, $message, $assert)
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage($method, $message);

        $this->assertSame($assert, $result);
    }

    /**
     * @TODO test with complex type into complex type
     * @TODO test setter and getter
     */
    public function testProcessMessageWithComplexType()
    {
        $definitionComplexTypes = $this->getDefinitionComplexTypes();

        $foo = new \stdClass();
        $foo->bar = 'foobar';
        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_argument', null, array(
                new Argument('foo', new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Foo')),
            )),
            array($foo),
            $definitionComplexTypes
        );

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Foo', $result['foo']);
        $this->assertEquals('foobar', $result['foo']->bar);

        $foobar  = new \stdClass();
        $foobar->bar = 'foobar';
        $barfoo  = new \stdClass();
        $barfoo->bar = 'barfoo';
        $message = new \stdClass();
        $message->item = array($foobar, $barfoo);
        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_argument', null, array(
                new Argument('foo', new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Foo[]')),
            )),
            array($message),
            $definitionComplexTypes
        );

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Foo', $result['foo'][0]);
        $this->assertEquals('foobar', $result['foo'][0]->bar);
        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Foo', $result['foo'][1]);
        $this->assertEquals('barfoo', $result['foo'][1]->bar);
    }

    public function messageProvider()
    {
        $messages = array();

        $messages[] = array(
            new Method('no_argument'),
            array(),
            array(),
        );

        $messages[] = array(
            new Method('string_argument', null, array(
                new Argument('foo', new Type('string')),
            )),
            array('bar'),
            array('foo' => 'bar'),
        );

        $messages[] = array(
            new Method('string_int_arguments', null, array(
                new Argument('foo', new Type('string')),
                new Argument('bar', new Type('int')),
            )),
            array('test', 20),
            array('foo' => 'test', 'bar' => 20),
        );

        $strings = new \stdClass();
        $strings->item = array('foo', 'bar', 'barfoo');
        $messages[] = array(
            new Method('array_string_arguments', null, array(
                new Argument('foo', new Type('string[]')),
                new Argument('bar', new Type('int')),
            )),
            array($strings, 4),
            array('foo' => array('foo', 'bar', 'barfoo'), 'bar' => 4),
        );

        return $messages;
    }

    public function getDefinitionComplexTypes()
    {
        $this->definitionComplexTypes = array();

        $collection = new Collection('getName');
        $property = new PropertyComplexType();
        $property->setName('bar');
        $property->setValue('string');
        $collection->add($property);
        $this->definitionComplexTypes['\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Foo'] = $collection;

        return $this->definitionComplexTypes;
    }
}
