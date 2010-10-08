<?php

namespace Bundle\WebServiceBundle\ServiceBinding;

use Bundle\WebServiceBundle\ServiceDefinition\Method;

interface MessageBinderInterface
{
    /**
     *
     *
     * @param Method $messageDefinition
     * @param mixed $message
     *
     * @return mixed
     */
    function processMessage(Method $messageDefinition, $message);
}