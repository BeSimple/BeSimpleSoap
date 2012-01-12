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

use Zend\Soap\Wsdl;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class RpcLiteralResponseMessageBinder implements MessageBinderInterface
{
    private $messageRefs = array();
    private $definitionComplexTypes;

    public function processMessage(Method $messageDefinition, $message, array $definitionComplexTypes = array())
    {
        $this->definitionComplexTypes = $definitionComplexTypes;

        return $this->processType($messageDefinition->getReturn()->getPhpType(), $message);
    }

    private function processType($phpType, $message)
    {
        $isArray = false;

        if (preg_match('/^([^\[]+)\[\]$/', $phpType, $match)) {
            $isArray = true;
            $phpType = $match[1];
        }

        if (isset($this->definitionComplexTypes[$phpType])) {
            if ($isArray) {
                $array = array();

                foreach ($message as $complexType) {
                    $array[] = $this->checkComplexType($phpType, $complexType);
                }

                $message = $array;
            } else {
                $message = $this->checkComplexType($phpType, $message);
            }
        }

        return $message;
    }

    private function checkComplexType($phpType, $message)
    {
        $hash = spl_object_hash($message);
        if (isset($this->messageRefs[$hash])) {
            return $this->messageRefs[$hash];
        }

        $this->messageRefs[$hash] = $message;

        if (!$message instanceof $phpType) {
            throw new \InvalidArgumentException(sprintf('The instance class must be "%s", "%s" given.', get_class($message), $phpType));
        }

        $r = new \ReflectionClass($message);
        foreach ($this->definitionComplexTypes[$phpType] as $type) {
            $p = $r->getProperty($type->getName());
            if ($p->isPublic()) {
                $value = $message->{$type->getName()};
            } else {
                $p->setAccessible(true);
                $value = $p->getValue($message);
            }

            if (null !== $value) {
                $value = $this->processType($type->getValue(), $value);

                if ($p->isPublic()) {
                    $message->{$type->getName()} = $value;
                } else {
                    $p->setValue($message, $value);
                }
            }

            if (!$type->isNillable() && null === $value) {
                throw new \InvalidArgumentException(sprintf('"%s::%s" cannot be null.', $phpType, $type->getName()));
            }
        }

        return $message;
    }
}
