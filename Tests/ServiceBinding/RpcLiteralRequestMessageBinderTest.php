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