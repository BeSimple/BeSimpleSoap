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

use Zend\Soap\Wsdl;

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

        $r = new \ReflectionClass($message);
        foreach ($this->definitionComplexTypes[$phpType] as $type) {
            $p = $r->getProperty($type->getName());
            if ($p->isPublic()) {
                $value = $message->{$type->getName()};
            } else {
                $p->setAccessible(true);
                $value = $p->getValue($message);
            }

            if ($value !== null) {
                $value = $this->processType($type->getValue(), $value);
                $p->setValue($message, $value);
            }

            if (!$type->isNillable() && null === $value) {
                throw new \SoapFault('SOAP_ERROR_COMPLEX_TYPE', sprintf('"%s:%s" cannot be null.', ucfirst(Wsdl::translateType($phpType)), $type->getName()));
            }
        }

        return $message;
    }
}