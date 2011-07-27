<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceBinding;

use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\PropertyComplexType;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\MethodComplexType;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class RpcLiteralResponseMessageBinder implements MessageBinderInterface
{
    private $messageRefs = array();

    public function processMessage(Method $messageDefinition, $message, array $definitionComplexTypes = array())
    {
        $return = $messageDefinition->getReturn();
        $class  = $return->getPhpType();

        if (preg_match('/^([^\[]+)\[\]$/', $class, $match)) {
            $isArray = true;
            $type    =
            $class   = $match[1];
        } else {
            $isArray = false;
            $type    = $return->getPhpType();
        }

        if (isset($definitionComplexTypes[$type])) {
            if ($class[0] == '\\') {
                $class = substr($class, 1);
            }

            if ($isArray) {
                $array = array();

                foreach ($message as $complexType) {
                    $array[] = $this->getInstanceOfStdClass($type, $class, $complexType, $definitionComplexTypes);
                }

                $message = $array;
            } else {
                $message = $this->getInstanceOfStdClass($type, $class, $message, $definitionComplexTypes);
            }
        }

        return $message;
    }

    private function getInstanceOfStdClass($type, $class, $message, $definitionComplexTypes)
    {
        if (get_class($message) !== $class) {
            throw new \InvalidArgumentException();
        }

        $hash = spl_object_hash($message);
        if (isset($this->messageRefs[$hash])) {
            return $this->messageRefs[$hash];
        }

        $stdClass = new \stdClass();
        $this->messageRefs[$hash] = $stdClass;

        foreach ($definitionComplexTypes[$type] as $type) {
            if ($type instanceof PropertyComplexType) {
                $stdClass->{$type->getName()} = $message->{$type->getOriginalName()};
            } elseif ($type instanceof MethodComplexType) {
                $stdClass->{$type->getName()} = $message->{$type->getOriginalName()}();
            } else {
                throw new \InvalidArgumentException();
            }
        }

        return $stdClass;
    }
}