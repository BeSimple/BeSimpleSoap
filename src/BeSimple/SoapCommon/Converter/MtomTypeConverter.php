<?php

/*
 * This file is part of the BeSimpleSoapClient.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Converter;

use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\Mime\Part as MimePart;
use BeSimple\SoapCommon\SoapKernel;
use BeSimple\SoapCommon\Converter\SoapKernelAwareInterface;
use BeSimple\SoapCommon\Converter\TypeConverterInterface;

/**
 * MTOM type converter.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class MtomTypeConverter implements TypeConverterInterface, SoapKernelAwareInterface
{
    /**
     * @var \BeSimple\SoapCommon\SoapKernel $soapKernel SoapKernel instance
     */
    protected $soapKernel = null;

    /**
     * {@inheritDoc}
     */
    public function getTypeNamespace()
    {
        return 'http://www.w3.org/2001/XMLSchema';
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeName()
    {
        return 'base64Binary';
    }

    /**
     * {@inheritDoc}
     */
    public function convertXmlToPhp($data)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($data);

        $includes = $doc->getElementsByTagNameNS(Helper::NS_XOP, 'Include');
        $include = $includes->item(0);

        // convert href -> myhref for external references as PHP throws exception in this case
        // http://svn.php.net/viewvc/php/php-src/branches/PHP_5_4/ext/soap/php_encoding.c?view=markup#l3436
        $ref = $include->getAttribute('myhref');

        if ('cid:' === substr($ref, 0, 4)) {
            $contentId = urldecode(substr($ref, 4));

            if (null !== ($part = $this->soapKernel->getAttachment($contentId))) {

                return $part->getContent();
            } else {

                return null;
            }
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function convertPhpToXml($data)
    {
        $part = new MimePart($data);
        $contentId = trim($part->getHeader('Content-ID'), '<>');

        $this->soapKernel->addAttachment($part);

        $doc = new \DOMDocument();
        $node = $doc->createElement($this->getTypeName());
        $doc->appendChild($node);

        // add xop:Include element
        $xinclude = $doc->createElementNS(Helper::NS_XOP, Helper::PFX_XOP . ':Include');
        $xinclude->setAttribute('href', 'cid:' . $contentId);
        $node->appendChild($xinclude);

        return $doc->saveXML();
    }

    /**
    * {@inheritDoc}
    */
    public function setKernel(SoapKernel $soapKernel)
    {
        $this->soapKernel = $soapKernel;
    }
}
