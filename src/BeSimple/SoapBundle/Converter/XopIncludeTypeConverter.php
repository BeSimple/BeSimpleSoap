<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Converter;

use BeSimple\SoapBundle\Soap\SoapRequest;
use BeSimple\SoapBundle\Util\String;
use BeSimple\SoapCommon\Converter\RequestAwareInterface;
use BeSimple\SoapCommon\Converter\TypeConverterInterface;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class XopIncludeTypeConverter implements TypeConverterInterface, RequestAwareInterface
{
    /**
     * @var SoapRequest
     */
    protected $request;

    /**
     * {@inheritdoc}
     */
    function setRequest(SoapRequest $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeNamespace()
    {
        return 'http://www.w3.org/2001/XMLSchema';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return 'base64Binary';
    }

    /**
     * {@inheritdoc}
     */
    public function convertXmlToPhp($data)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($data);

        $includes = $doc->getElementsByTagNameNS('http://www.w3.org/2004/08/xop/include', 'Include');
        $include = $includes->item(0);

        $ref = $include->getAttribute('href');

        if (String::startsWith($ref, 'cid:')) {
            $cid = urldecode(substr($ref, 4));

            if (!$this->request) {
                throw new \InvalidArgumentException('Request is missing');
            }

            return $this->request->getSoapAttachments()->get($cid)->getContent();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function convertPhpToXml($data)
    {
        return $data;
    }
}
