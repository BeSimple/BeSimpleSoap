<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceBinding;

use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\MethodComplexType;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\PropertyComplexType;
use BeSimple\SoapCommon\Util\MessageBinder;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class RpcLiteralRequestMessageBinder implements MessageBinderInterface
{
    private $messageRefs = array();
    private $definitionComplexTypes;

    public function processMessage(Method $messageDefinition, $message, array $definitionComplexTypes = array())
    {
        $this->definitionComplexTypes = $definitionComplexTypes;

        $result = array();
        $i      = 0;

        foreach ($messageDefinition->getArguments() as $argument) {
            if (isset($message[$i])) {
                $result[$argument->getName()] = $this->processType($argument->getType()->getPhpType(), $message[$i]);
            }

            $i++;
        }

        return $result;
    }

    protected function processType($phpType, $message)
    {
        $isArray = false;

        if (preg_match('/^([^\[]+)\[\]$/', $phpType, $match)) {
            $isArray = true;
            $array   = array();
            $phpType = $match[1];
        }

        // @TODO Fix array reference
        if (isset($this->definitionComplexTypes[$phpType])) {
            if ($isArray) {
                if (isset($message->item)) {
                    foreach ($message->item as $complexType) {
                        $array[] = $this->checkComplexType($phpType, $complexType);
                    }

                    // See https://github.com/BeSimple/BeSimpleSoapBundle/issues/29
                    if (in_array('BeSimple\SoapCommon\Type\AbstractKeyValue', class_parents($phpType))) {
                        $assocArray = array();
                        foreach ($array as $keyValue) {
                            $assocArray[$keyValue->getKey()] = $keyValue->getValue();
                        }

                        $array = $assocArray;
                    }
                }

                $message = $array;
            } else {
                $message = $this->checkComplexType($phpType, $message);
            }
        } elseif ($isArray) {
            if (isset($message->item)) {
                $message = $message->item;
            } else {
                $message = $array;
            }
        }

        return $message;
    }

    protected function checkComplexType($phpType, $message)
    {
        $hash = spl_object_hash($message);
        if (isset($this->messageRefs[$hash])) {
            return $this->messageRefs[$hash];
        }

        $this->messageRefs[$hash] = $message;

        $messageBinder = new MessageBinder($message);
        foreach ($this->definitionComplexTypes[$phpType] as $type) {
            $property = $type->getName();
            $value = $messageBinder->readProperty($property);

            if (null !== $value) {
                $value = $this->processType($type->getValue(), $value);

                $messageBinder->writeProperty($property, $value);
            }

            if (!$type->isNillable() && null === $value) {
                throw new \SoapFault('SOAP_ERROR_COMPLEX_TYPE', sprintf('"%s:%s" cannot be null.', ucfirst($phpType), $type->getName()));
            }
        }

        return $message;
    }
}
