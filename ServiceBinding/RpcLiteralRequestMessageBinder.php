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
use BeSimple\SoapBundle\ServiceDefinition\Strategy\MethodComplexType;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\PropertyComplexType;

class RpcLiteralRequestMessageBinder implements MessageBinderInterface
{
    public function processMessage(Method $messageDefinition, $message, array $definitionComplexTypes = array())
    {
        $result = array();
        $i      = 0;

        foreach($messageDefinition->getArguments() as $argument) {
            if (isset($message[$i])) {
                if (preg_match('/^([^\[]+)\[\]$/', $argument->getType()->getPhpType(), $match)) {
                    $isArray = true;
                    $type    = $match[1];
                } else {
                    $isArray = false;
                    $type    = $argument->getType()->getPhpType();
                }

                if (isset($definitionComplexTypes[$type])) {
                    if ($isArray) {
                        $array = array();

                        foreach ($message[$i]->item as $complexType) {
                            $array[] = $this->getInstanceOfType($type, $complexType, $definitionComplexTypes);
                        }

                        $message[$i] = $array;
                    } else {
                        $message[$i] = $this->getInstanceOfType($type, $message[$i], $definitionComplexTypes);
                    }
                } elseif ($isArray) {
                    $message[$i] = $message[$i]->item;
                }

                $result[$argument->getName()] = $message[$i];
            }

            $i++;
        }

        return $result;
    }

    private function getInstanceOfType($type, $message, array $definitionComplexTypes)
    {
        $typeClass    = $type;
        $instanceType = new $typeClass();

        foreach ($definitionComplexTypes[$type] as $type) {
            if ($type instanceof PropertyComplexType) {
                if (isset($definitionComplexTypes[$type->getValue()])) {
                    $value = $this->getInstanceOfType($type->getValue(), $message->{$type->getName()}, $definitionComplexTypes);
                } else {
                    $value = $message->{$type->getName()};
                }

                $instanceType->{$type->getOriginalName()} = $value;
            } elseif ($type instanceof MethodComplexType) {
                if (!$type->getSetter()) {
                    throw new \LogicException();
                }

                if (isset($definitionComplexTypes[$type->getValue()])) {
                    $value = $this->getInstanceOfType($type->getValue(), $message->{$type->getName()}, $definitionComplexTypes);
                } else {
                    $value = $message->{$type->getName()};
                }

                $instanceType->{$type->getSetter()}($value);
            } else {
                throw new \InvalidArgumentException();
            }
        }

        return $instanceType;
    }
}