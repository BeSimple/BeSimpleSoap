<?php

namespace Bundle\WebServiceBundle\ServiceBinding;

use Bundle\WebServiceBundle\ServiceDefinition\Method;

class DocumentLiteralWrappedResponseMessageBinder implements MessageBinderInterface
{
    public function processMessage(Method $messageDefinition, $message)
    {
        $result = new \stdClass();
        $result->{$messageDefinition->getName() . 'Result'} = $message;

        return $result;
    }
}