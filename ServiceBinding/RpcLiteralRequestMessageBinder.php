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

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class RpcLiteralRequestMessageBinder implements MessageBinderInterface
{
    private $messageRefs = array();

    public function processMessage(Method $messageDefinition, $message, array $definitionComplexTypes = array())
    {
        $result = array();
        $i      = 0;

        foreach ($messageDefinition->getArguments() as $argument) {
            if (isset($message[$i])) {
                $result[$argument->getName()] = $this->processType($argument->getType()->getPhpType(), $message[$i], $definitionComplexTypes);
            }

            $i++;
        }

        return $result;
    }

    protected function processType($phpType, $message, array $definitionComplexTypes)
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

                foreach ($message->item as $complexType) {
                    $array[] = $this->getInstanceOfType($type, $complexType, $definitionComplexTypes);
                }

                $message = $array;
            } else {
                $message = $this->getInstanceOfType($type, $message, $definitionComplexTypes);
            }
        } elseif ($isArray) {
            $message = $message->item;
        }

        return $message;
    }

    private function getInstanceOfType($phpType, $message, array $definitionComplexTypes)
    {
        $hash = spl_object_hash($message);
        if (isset($this->messageRefs[$hash])) {
            return $this->messageRefs[$hash];
        }

        $this->messageRefs[$hash] =
        $instanceType             = new $phpType();

        foreach ($definitionComplexTypes[$phpType] as $type) {
            $value = $this->processType($type->getValue(), $message->{$type->getName()}, $definitionComplexTypes);

            if (null === $value && $type->isNillable()) {
                continue;
            }

            if ($type instanceof PropertyComplexType) {
                $instanceType->{$type->getOriginalName()} = $value;
            } elseif ($type instanceof MethodComplexType) {
                if (!$type->getSetter()) {
                    throw new \LogicException(sprintf('"setter" option must be specified to hydrate "%s::%s()"', $phpType, $type->getOriginalName()));
                }

                $instanceType->{$type->getSetter()}($value);
            } else {
                throw new \InvalidArgumentException();
            }
        }

        return $instanceType;
    }
}