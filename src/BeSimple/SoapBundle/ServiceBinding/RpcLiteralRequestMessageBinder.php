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
use BeSimple\SoapCommon\Definition\Type\ArrayOfType;
use BeSimple\SoapCommon\Definition\Type\ComplexType;
use BeSimple\SoapCommon\Definition\Type\TypeRepository;
use BeSimple\SoapCommon\Util\MessageBinder;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class RpcLiteralRequestMessageBinder implements MessageBinderInterface
{
    protected $typeRepository;

    private $messageRefs = array();

    public function processMessage(Method $messageDefinition, $message, TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;

        $result = array();
        $i      = 0;

        foreach ($messageDefinition->getInput()->all() as $argument) {
            if (isset($message[$i])) {
                $result[$argument->getName()] = $this->processType($argument->getType(), $message[$i]);
            }

            $i++;
        }

        return $result;
    }

    protected function processType($phpType, $message)
    {
        $isArray = false;

        $type = $this->typeRepository->getType($phpType);
        if ($type instanceof ArrayOfType) {
            $isArray = true;
            $array = array();

            $type = $this->typeRepository->getType($type->get('item')->getType());
        }

        // @TODO Fix array reference
        if ($type instanceof ComplexType) {
            $phpType = $type->getPhpType();

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
        foreach ($this->typeRepository->getType($phpType)->all() as $type) {
            $property = $type->getName();
            $value = $messageBinder->readProperty($property);

            if (null !== $value) {
                $value = $this->processType($type->getType(), $value);

                $messageBinder->writeProperty($property, $value);
            } elseif (!$type->isNillable()) {
                // @TODO use xmlType instead of phpType
                throw new \SoapFault('SOAP_ERROR_COMPLEX_TYPE', sprintf('"%s:%s" cannot be null.', ucfirst($phpType), $type->getName()));
            }
        }

        return $message;
    }
}
