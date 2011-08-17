<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Dumper;

use Zend\Soap\Wsdl as BaseWsdl;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Wsdl extends BaseWsdl
{
    public function addBindingOperationHeader(\DOMElement $bindingOperation, array $headers, array $baseBinding)
    {
        foreach ($headers as $header) {
            $inputNode  = $bindingOperation->getElementsByTagName('input')->item(0);

            $headerNode = $this->toDomDocument()->createElement('soap:header');
            $headerNode->setAttribute('part', $header);

            foreach ($baseBinding as $name => $value) {
                $headerNode->setAttribute($name, $value);
            }

            $inputNode->appendChild($headerNode);
        }

        return $bindingOperation;
    }
}