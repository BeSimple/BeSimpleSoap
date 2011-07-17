<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceBinding;

use Bundle\WebServiceBundle\ServiceDefinition\Method;

class RpcLiteralRequestMessageBinder implements MessageBinderInterface
{
    public function processMessage(Method $messageDefinition, $message)
    {
        $result = array();
        $i      = 0;

        foreach($messageDefinition->getArguments() as $argument) {
            if (isset($message[$i])) {
                $result[$argument->getName()] = $message[$i];
            }

            $i++;
        }

        return $result;
    }
}