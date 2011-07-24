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
    private $definitionComplexTypes;

    public function processMessage(Method $messageDefinition, $message, array $definitionComplexTypes = array())
    {
        $this->definitionComplexTypes = $definitionComplexTypes;

        $result = array();
        $i      = 0;

        foreach($messageDefinition->getArguments() as $argument) {
            if (isset($message[$i])) {
                $result[$argument->getName()] = $this->processType($argument->getType()->getPhpType(), $message[$i]);
            }

            $i++;
        }

        $this->definitionComplexTypes = array();

        return $result;
    }

    private function processType($phpType, $message)
    {
        if (preg_match('/^([^\[]+)\[\]$/', $phpType, $match)) {
            $isArray = true;
            $type    = $match[1];
        } else {
            $isArray = false;
            $type    = $phpType;
        }

        if (isset($this->definitionComplexTypes[$type])) {
            if ($isArray) {
                $array = array();

                foreach ($message->item as $complexType) {
                    $array[] = $this->getInstanceOfType($type, $complexType);
                }

                $message = $array;
            } else {
                $message = $this->getInstanceOfType($type, $message);
            }
        } elseif ($isArray) {
            $message = $message->item;
        }

        return $message;
    }

    private function getInstanceOfType($phpType, $message)
    {
        $instanceType = new $phpType();

        foreach ($this->definitionComplexTypes[$phpType] as $type) {
            $value = $this->processType($type->getValue(), $message->{$type->getName()});

            if ($type instanceof PropertyComplexType) {
                $instanceType->{$type->getOriginalName()} = $value;
            } elseif ($type instanceof MethodComplexType) {
                if (!$type->getSetter()) {
                    throw new \LogicException();
                }

                $instanceType->{$type->getSetter()}($value);
            } else {
                throw new \InvalidArgumentException();
            }
        }

        return $instanceType;
    }
}