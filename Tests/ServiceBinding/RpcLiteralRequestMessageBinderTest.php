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
use BeSimple\SoapBundle\ServiceDefinition\Strategy\MethodComplexType;
use BeSimple\SoapBundle\ServiceBinding\RpcLiteralRequestMessageBinder;
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

    public function testProcessMessageWithComplexType()
    {
        $attributes = new \stdClass();
        $attributes->foo = 'bar';
        $attributes->bar = 10;

        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_argument', null, array(), array(
                new Argument('attributes', new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes')),
            )),
            array($attributes),
            $this->getDefinitionComplexTypes()
        );

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes', $result['attributes']);
        $this->assertSame('bar', $result['attributes']->foo);
        $this->assertSame(10, $result['attributes']->bar);

        $attributes1 = new \stdClass();
        $attributes1->foo = 'foobar';
        $attributes1->bar = 11;
        $attributes2  = new \stdClass();
        $attributes2->foo = 'barfoo';
        $attributes2->bar = 12;

        $message = new \stdClass();
        $message->item = array($attributes1, $attributes2);

        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_argument', null, array(), array(
                new Argument('attributes', new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes[]')),
            )),
            array($message),
            $this->getDefinitionComplexTypes()
        );

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes', $result['attributes'][0]);
        $this->assertSame('foobar', $result['attributes'][0]->foo);
        $this->assertSame(11, $result['attributes'][0]->bar);
        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes', $result['attributes'][1]);
        $this->assertSame('barfoo', $result['attributes'][1]->foo);
        $this->assertSame(12, $result['attributes'][1]->bar);
    }

    public function testProcessMessageWithComplexTypeMethods()
    {
        $methods = new \stdClass();
        $methods->foo = 'bar';
        $methods->bar = 23;

        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_methods', null, array(), array(
                new Argument('setters', new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Setters')),
            )),
            array($methods),
            $this->getDefinitionComplexTypes()
        );

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Setters', $result['setters']);
        $this->assertSame('bar', $result['setters']->getFoo());
        $this->assertSame(23, $result['setters']->getBar());
    }

    public function testProcessMessageWithComplexTypeIntoComplexType()
    {
        $complexType = new \stdClass();
        $foo = $complexType->foo = new \stdClass();
        $foo->foo = 'hello';
        $foo->bar = 24;

        $bar = $complexType->bar = new \stdClass();
        $bar->foo = 'bonjour';
        $bar->bar = 1012;

        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextype_complextype', null, array(), array(
                new Argument('complex_type', new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\ComplexType')),
            )),
            array($complexType),
            $this->getDefinitionComplexTypes()
        );

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\ComplexType', $result['complex_type']);

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Attributes', $result['complex_type']->getFoo());
        $this->assertSame('hello', $result['complex_type']->getFoo()->foo);
        $this->assertSame(24, $result['complex_type']->getFoo()->bar);

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\Setters', $result['complex_type']->bar);
        $this->assertSame('bonjour', $result['complex_type']->bar->getFoo());
        $this->assertSame(1012, $result['complex_type']->bar->getBar());
    }

    public function testProcessMessageWithComplexTypeReferences()
    {
        $complexType1 = new \stdClass();
        $foo = $complexType1->foo = new \stdClass();
        $foo->foo = 'hello';
        $foo->bar = 24;

        $bar = $complexType1->bar = new \stdClass();
        $bar->foo = 'bonjour';
        $bar->bar = 1012;

        $complexType2 = new \stdClass();
        $complexType2->foo = $foo;
        $complexType2->bar = $bar;

        $complexTypes = new \stdClass();
        $complexTypes->item = array($complexType1, $complexType2);

        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage(
            new Method('complextypes_references', null, array(), array(
                new Argument('complex_types', new Type('\BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\ComplexType[]')),
            )),
            array($complexTypes),
            $this->getDefinitionComplexTypes()
        );

        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\ComplexType', $result['complex_types'][0]);
        $this->assertInstanceOf('BeSimple\SoapBundle\Tests\ServiceBinding\fixtures\ComplexType', $result['complex_types'][1]);

        $this->assertSame($result['complex_types'][0]->getFoo(), $result['complex_types'][1]->getFoo());
        $this->assertSame($result['complex_types'][0]->bar, $result['complex_types'][1]->bar);
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
            new Method('string_argument', null, array(), array(
                new Argument('foo', new Type('string')),
            )),
            array('bar'),
            array('foo' => 'bar'),
        );

        $messages[] = array(
            new Method('string_int_arguments', null, array(), array(
                new Argument('foo', new Type('string')),
                new Argument('bar', new Type('int')),
            )),
            array('test', 20),
            array('foo' => 'test', 'bar' => 20),
        );

        $strings = new \stdClass();
        $strings->item = array('foo', 'bar', 'barfoo');
        $messages[] = array(
            new Method('array_string_arguments', null, array(), array(
                new Argument('foo', new Type('string[]')),
                new Argument('bar', new Type('int')),
            )),
            array($strings, 4),
            array('foo' => array('foo', 'bar', 'barfoo'), 'bar' => 4),
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
