<?php

namespace Bundle\WebServiceBundle\ServiceBinding;

use Bundle\WebServiceBundle\ServiceDefinition\Method;

class DocumentLiteralWrappedRequestMessageBinder implements MessageBinderInterface
{
    public function processMessage(Method $messageDefinition, $message)
    {
        if(count($message) > 1)
        {
            throw new \InvalidArgumentException();
        }

        $result = array();
        $message = $message[0];

        foreach($messageDefinition->getArguments() as $argument)
        {
            $result[$argument->getName()] = $message->{$argument->getName()};
        }

        return $result;
    }
}