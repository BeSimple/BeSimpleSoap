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

use BeSimple\SoapBundle\ServiceBinding\RpcLiteralRequestMessageBinder;
use BeSimple\SoapBundle\ServiceDefinition as Definition;
use BeSimple\SoapBundle\Tests\fixtures\ServiceBinding as Fixtures;
use BeSimple\SoapBundle\Util\Collection;

class RpcLiteralRequestMessageBinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider messageProvider
     */
    public function testProcessMessage(Definition\Method $method, $message, $assert)
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();
        $result        = $messageBinder->processMessage($method, $message);

        $this->assertSame($assert, $result);
    }

    public function testProcessMessageWithComplexType()
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();

        $foo    = new Fixtures\Foo('foobar', 19395);
        $result = $messageBinder->processMessage(
            new Definition\Method('complextype_argument', null, array(), array(
                new Definition\Argument('foo', new Definition\Type('BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Foo')),
            )),
            array($foo),
            $this->getDefinitionComplexTypes()
        );

        $this->assertEquals(array('foo' => $foo), $result);


        $foo1 = new Fixtures\Foo('foobar', 29291);
        $foo2 = new Fixtures\Foo('barfoo', 39392);
        $foos = new \stdClass();
        $foos->item = array($foo1, $foo2);

        $result = $messageBinder->processMessage(
            new Definition\Method('complextype_argument', null, array(), array(
                new Definition\Argument('foos', new Definition\Type('BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Foo[]')),
            )),
            array($foos),
            $this->getDefinitionComplexTypes()
        );

        $this->assertEquals(array('foos' => array($foo1, $foo2)), $result);
    }

    /**
     * @expectedException SoapFault
     */
    public function testProcessMessageSoapFault()
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();

        $foo = new Fixtures\Foo('foo', null);
        $result = $messageBinder->processMessage(
            new Definition\Method('complextype_argument', null, array(), array(
                new Definition\Argument('foo', new Definition\Type('BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Foo')),
            )),
            array($foo),
            $this->getDefinitionComplexTypes()
        );
    }

    public function testProcessMessageWithComplexTypeReference()
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();

        $foo  = new Fixtures\Foo('foo', 2499104);
        $foos = new \stdClass();
        $foos->item = array($foo, $foo);

        $result = $messageBinder->processMessage(
            new Definition\Method('complextype_argument', null, array(), array(
                new Definition\Argument('foos', new Definition\Type('BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Foo[]')),
            )),
            array($foos),
            $this->getDefinitionComplexTypes()
        );

        $this->assertEquals(array('foos' => array($foo, $foo)), $result);
    }

    public function testProcessMessageWithComplexTypeIntoComplexType()
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();

        $foo    = new Fixtures\Foo('foo', 38845);
        $bar    = new Fixtures\Bar('bar', null);
        $fooBar = new Fixtures\FooBar($foo, $bar);

        $result = $messageBinder->processMessage(
            new Definition\Method('complextype_argument', null, array(), array(
                new Definition\Argument('fooBar', new Definition\Type('BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\FooBar')),
            )),
            array($fooBar),
            $this->getDefinitionComplexTypes()
        );

        $this->assertEquals(array('fooBar' => $fooBar), $result);
    }

    public function testProcessMessageComplexTypeWithArrays()
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();

        $array          = array(1, 2, 3, 4);
        $stdClass       = new \stdClass();
        $stdClass->item = $array;
        $simpleArrays   = new Fixtures\SimpleArrays(null, new \stdClass(), $stdClass);

        $result = $messageBinder->processMessage(
            new Definition\Method('complextype_with_array', null, array(), array(
                new Definition\Argument('simple_arrays', new Definition\Type('BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\SimpleArrays')),
            )),
            array($simpleArrays),
            $this->getDefinitionComplexTypes()
        );

        $result = $result['simple_arrays'];
        $this->assertEquals(null, $result->array1);
        $this->assertEquals(array(), $result->getArray2());
        $this->assertEquals($array, $result->getArray3());
    }

    public function testProcessMessageWithEmptyArrayComplexType()
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();

        $result = $messageBinder->processMessage(
            new Definition\Method('empty_array_complex_type', null, array(), array(
                new Definition\Argument('foo', new Definition\Type('BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Foo[]')),
            )),
            array(new \stdClass()),
            $this->getDefinitionComplexTypes()
        );

        $this->assertEquals(array('foo' => array()), $result);
    }

    public function testProccessMessagePreventInfiniteRecursion()
    {
        $messageBinder = new RpcLiteralRequestMessageBinder();

        $foo = new Fixtures\FooRecursive('foo', '');
        $bar = new Fixtures\BarRecursive($foo, 10394);
        $foo->bar = $bar;

        $result = $messageBinder->processMessage(
            new Definition\Method('prevent_infinite_recursion', null, array(), array(
                new Definition\Argument('foo_recursive', new Definition\Type('BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\FooRecursive')),
            )),
            array($foo),
            $this->getDefinitionComplexTypes()
        );

        $this->assertEquals(array('foo_recursive' => $foo), $result);
    }

    public function messageProvider()
    {
        $messages = array();

        $messages[] = array(
            new Definition\Method('no_argument'),
            array(),
            array(),
        );

        $messages[] = array(
            new Definition\Method('string_argument', null, array(), array(
                new Definition\Argument('foo', new Definition\Type('string')),
            )),
            array('bar'),
            array('foo' => 'bar'),
        );

        $messages[] = array(
            new Definition\Method('string_int_arguments', null, array(), array(
                new Definition\Argument('foo', new Definition\Type('string')),
                new Definition\Argument('bar', new Definition\Type('int')),
            )),
            array('test', 20),
            array('foo' => 'test', 'bar' => 20),
        );

        $strings = new \stdClass();
        $strings->item = array('foo', 'bar', 'barfoo');
        $messages[] = array(
            new Definition\Method('array_string_arguments', null, array(), array(
                new Definition\Argument('foo', new Definition\Type('string[]')),
                new Definition\Argument('bar', new Definition\Type('int')),
            )),
            array($strings, 4),
            array('foo' => array('foo', 'bar', 'barfoo'), 'bar' => 4),
        );

        $messages[] = array(
            new Definition\Method('empty_array', null, array(), array(
                new Definition\Argument('foo', new Definition\Type('string[]')),
            )),
            array(new \stdClass()),
            array('foo' => array()),
        );

        return $messages;
    }

    private function getDefinitionComplexTypes()
    {
        $definitionComplexTypes = array();

        $definitionComplexTypes['BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Foo'] = $this->createComplexTypeCollection(array(
            array('foo', 'string'),
            array('bar', 'int'),
        ));

        $definitionComplexTypes['BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Bar'] = $this->createComplexTypeCollection(array(
            array('foo', 'string'),
            array('bar', 'int', true),
        ));

        $definitionComplexTypes['BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\FooBar'] = $this->createComplexTypeCollection(array(
            array('foo', 'BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Foo'),
            array('bar', 'BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\Bar'),
        ));

        $definitionComplexTypes['BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\SimpleArrays'] = $this->createComplexTypeCollection(array(
            array('array1', 'string[]', true),
            array('array2', 'string[]'),
            array('array3', 'string[]'),
        ));

        $definitionComplexTypes['BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\FooRecursive'] = $this->createComplexTypeCollection(array(
            array('bar', 'BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\BarRecursive'),
        ));

        $definitionComplexTypes['BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\BarRecursive'] = $this->createComplexTypeCollection(array(
            array('foo', 'BeSimple\SoapBundle\Tests\fixtures\ServiceBinding\FooRecursive'),
        ));

        return $definitionComplexTypes;
    }

    private function createComplexTypeCollection(array $properties)
    {
        $collection = new Collection('getName', 'BeSimple\SoapBundle\ServiceDefinition\ComplexType');

        foreach ($properties as $property) {
            $complexType = new Definition\ComplexType();
            $complexType->setName($property[0]);
            $complexType->setValue($property[1]);

            if (isset($property[2])) {
                $complexType->setNillable($property[2]);
            }

            $collection->add($complexType);
        }

        return $collection;
    }
}
