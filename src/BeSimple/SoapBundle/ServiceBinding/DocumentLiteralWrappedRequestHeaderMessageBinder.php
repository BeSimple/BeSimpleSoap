<?php

namespace BeSimple\SoapBundle\ServiceBinding;

use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapCommon\Definition\Type\TypeRepository;
/**
 * Description of DocumentLiteralWrappedRequestHeaderBinder
 *
 * @author Michal Mičko <michal.micko@mediaaction.cz>
 */
class DocumentLiteralWrappedRequestHeaderMessageBinder extends DocumentLiteralWrappedRequestMessageBinder
{
    private $header;

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function processMessage(Method $messageDefinition, $message, TypeRepository $typeRepository)
    {
        $this->typeRepository = $typeRepository;
        $headerDefinition = $messageDefinition->getHeaders()->get($this->header);

        return $this->processType($headerDefinition->getType(), $message);
    }
}
