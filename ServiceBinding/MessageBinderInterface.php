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