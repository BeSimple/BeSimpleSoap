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

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class DocumentLiteralWrappedResponseMessageBinder implements MessageBinderInterface
{
    public function processMessage(Method $messageDefinition, $message)
    {
        $result = new \stdClass();
        $result->{$messageDefinition->getName().'Result'} = $message;

        return $result;
    }
}