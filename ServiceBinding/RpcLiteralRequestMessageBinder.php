<?php

namespace Bundle\WebServiceBundle\ServiceBinding;

use Bundle\WebServiceBundle\ServiceDefinition\Method;

class RpcLiteralRequestMessageBinder implements MessageBinderInterface
{
    public function processMessage(Method $messageDefinition, $message)
    {
        $result = array();
        $i = 0;

        foreach($messageDefinition->getArguments() as $argument)
        {
            $result[$argument->getName()] = $message[$i];
            $i++;
        }

        return $result;
    }
}