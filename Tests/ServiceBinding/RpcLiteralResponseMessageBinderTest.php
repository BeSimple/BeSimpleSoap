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
use BeSimple\SoapBundle\ServiceDefinition\Type;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\PropertyComplexType;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\MethodComplexType;
use BeSimple\SoapBundle\ServiceBinding\RpcLiteralResponseMessageBinder;
use BeSimple\SoapBundle\Util\Collection;
use BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes;
use BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\ComplexType;
use BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Setters;

/**
 * UnitTest for \BeSimple\SoapBundle\ServiceBinding\RpcLiteralRequestMessageBinder.
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class RpcLiteralResponseMessageBinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider messageProvider
     */
    public function testProcessMessage(Method $method, $message, $assert)
    {
        $messageBinder = new RpcLiteralResponseMessageBinder();
        $result        = $messageBinder->processMessage($method, $message);

        $this->assertSame($assert, $result);
    }

    public function testProcessMessageWithComplexType()
    {
        $definitionComplexTypes = $this->getDefinitionComplexTypes();

        $attributes = new Attributes();
        $attributes->foo = 'foobar';
        $attributes->bar = 20349;
        $messageBinder = new RpcLiteralResponseMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype', null, array(), array(), new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes')),
            $attributes,
            $definitionComplexTypes
        );

        $this->assertInstanceOf('stdClass', $result);
        $this->assertSame('foobar', $result->foo);
        $this->assertSame(20349, $result->bar);

        $attributes1 = new Attributes();
        $attributes1->foo = 'bar';
        $attributes1->bar = 2929;
        $attributes2  = new Attributes();
        $attributes2->foo = 'foo';
        $attributes2->bar = 123992;
        $message = array($attributes1, $attributes2);
        $messageBinder = new RpcLiteralResponseMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_argument', null, array(), array(), new  Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes[]')),
            $message,
            $definitionComplexTypes
        );

        $this->assertTrue(is_array($result));

        $this->assertInstanceOf('stdClass', $result[0]);
        $this->assertSame('bar', $result[0]->foo);
        $this->assertSame(2929, $result[0]->bar);

        $this->assertInstanceOf('stdClass', $result[1]);
        $this->assertSame('foo', $result[1]->foo);
        $this->assertSame(123992, $result[1]->bar);
    }

    public function testProcessMessageWithComplexTypeMethods()
    {
        $setters = new Setters();
        $setters->setFoo('foobar');
        $setters->setBar(42);

        $messageBinder = new RpcLiteralResponseMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_methods', null, array(), array(), new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Setters')),
            $setters,
            $this->getDefinitionComplexTypes()
        );

        $this->assertInstanceOf('stdClass', $result);
        $this->assertSame('foobar', $result->foo);
        $this->assertSame(42, $result->bar);
    }

    public function testProcessMessageWithComplexTypeIntoComplexType()
    {
        $complexType = new ComplexType();

        $foo = new Attributes();
        $foo->foo = 'Hello world!';
        $foo->bar = 4242;
        $complexType->setFoo($foo);

        $bar = new Setters();
        $bar->setFoo('bar foo');
        $bar->setBar(2424);
        $complexType->bar = $bar;

        $messageBinder = new RpcLiteralResponseMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_complextype', null, array(), array(), new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\ComplexType')),
            $complexType,
            $this->getDefinitionComplexTypes()
        );

        $this->assertInstanceOf('stdClass', $result);

        $this->assertInstanceOf('stdClass', $result->foo);
        $this->assertSame('Hello world!', $result->foo->foo);
        $this->assertSame(4242, $result->foo->bar);

        $this->assertInstanceOf('stdClass', $result->bar);
        $this->assertSame('bar foo', $result->bar->foo);
        $this->assertSame(2424, $result->bar->bar);
    }

    public function testProcessMessageWithComplexTypeReferences()
    {
        $attributes = new Attributes();
        $attributes->foo = 'bar';
        $attributes->bar = 2929;

        $message = array($attributes, $attributes);
        $messageBinder = new RpcLiteralResponseMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_argument', null, array(), array(), new  Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes[]')),
            $message,
            $this->getDefinitionComplexTypes()
        );

        $this->assertInstanceOf('stdClass', $result[0]);
        $this->assertSame($result[0], $result[1]);
    }

    public function messageProvider()
    {
        $messages = array();

        $messages[] = array(
            new Method('boolean', null, array(), array(), new Type('boolean')),
            true,
            true,
        );

        $messages[] = array(
            new Method('strings', null, array(), array(), new Type('string[]')),
            array('hello', 'world'),
            array('hello', 'world'),
        );

        return $messages;
    }

    private function getDefinitionComplexTypes()
    {
        $this->definitionComplexTypes = array();

        $this->definitionComplexTypes['\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes'] = $this->createPropertiesCollection(array(
            array('foo', 'string'),
            array('bar', 'int'),
        ));

        $this->definitionComplexTypes['\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Setters'] = $this->createMethodsCollection(array(
            array('foo', 'string', 'getFoo', 'setFoo'),
            array('bar', 'int', 'getBar', 'setBar'),
        ));

        $collection = $this->createMethodsCollection(array(
            array('foo', '\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes', 'getFoo', 'setFoo'),
        ));
        $this->createPropertiesCollection(array(
            array('bar', '\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Setters'),
        ), $collection);
        $this->definitionComplexTypes['\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\ComplexType'] = $collection;

        return $this->definitionComplexTypes;
    }

    private function createPropertiesCollection(array $properties, Collection $collection = null)
    {
        $collection = $collection ?: new Collection('getName');

        foreach ($properties as $property) {
            $collectionProperty = new PropertyComplexType();
            $collectionProperty->setName($property[0]);
            $collectionProperty->setValue($property[1]);

            $collection->add($collectionProperty);
        }

        return $collection;
    }

    private function createMethodsCollection(array $methods, Collection $collection = null)
    {
        $collection = $collection ?: new Collection('getName');

        foreach ($methods as $method) {
            $collectionMethod = new MethodComplexType();
            $collectionMethod->setName($method[0]);
            $collectionMethod->setValue($method[1]);
            $collectionMethod->setOriginalName($method[2]);
            $collectionMethod->setSetter($method[3]);

            $collection->add($collectionMethod);
        }

        return $collection;
    }
}
