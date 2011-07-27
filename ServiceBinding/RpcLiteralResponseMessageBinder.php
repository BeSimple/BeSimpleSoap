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

        $message = $this->processType($messageDefinition->getReturn()->getPhpType(), $message, $definitionComplexTypes);

        return $message;
    }

    private function processType($phpType, $message, array $definitionComplexTypes)
    {
        if (preg_match('/^([^\[]+)\[\]$/', $phpType, $match)) {
            $isArray = true;
            $type    = $match[1];
        } else {
            $isArray = false;
            $type    = $phpType;
        }

        if (isset($definitionComplexTypes[$type])) {
            if ($isArray) {
                $array = array();

                foreach ($message as $complexType) {
                    $array[] = $this->getInstanceOfStdClass($type, $complexType, $definitionComplexTypes);
                }

                $message = $array;
            } else {
                $message = $this->getInstanceOfStdClass($type, $message, $definitionComplexTypes);
            }
        }

        return $message;
    }

    private function getInstanceOfStdClass($phpType, $message, $definitionComplexTypes)
    {
        $hash = spl_object_hash($message);
        if (isset($this->messageRefs[$hash])) {
            return $this->messageRefs[$hash];
        }

        $class = $phpType;
        if ($class[0] == '\\') {
            $class = substr($class, 1);
        }

        if (get_class($message) !== $class) {
            throw new \InvalidArgumentException();
        }

        $stdClass = new \stdClass();
        $this->messageRefs[$hash] = $stdClass;

        foreach ($definitionComplexTypes[$phpType] as $type) {

            if ($type instanceof PropertyComplexType) {
                $value = $message->{$type->getOriginalName()};
            } elseif ($type instanceof MethodComplexType) {
                $value = $message->{$type->getOriginalName()}();
            } else {
                throw new \InvalidArgumentException();
            }

            $stdClass->{$type->getName()} = $this->processType($type->getValue(), $value, $definitionComplexTypes);
        }

        return $stdClass;
    }
}