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

namespace BeSimple\SoapClient;

use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\Mime\MultiPart as MimeMultiPart;
use BeSimple\SoapCommon\Mime\Parser as MimeParser;
use BeSimple\SoapCommon\Mime\Part as MimePart;
use BeSimple\SoapCommon\SoapRequest;
use BeSimple\SoapCommon\SoapRequestFilter;
use BeSimple\SoapCommon\SoapResponse;
use BeSimple\SoapCommon\SoapResponseFilter;

/**
 * MIME filter.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class MimeFilter implements SoapRequestFilter, SoapResponseFilter
{
    /**
     * Attachment type.
     *
     * @var int Helper::ATTACHMENTS_TYPE_SWA | Helper::ATTACHMENTS_TYPE_MTOM
     */
    protected $attachmentType = Helper::ATTACHMENTS_TYPE_SWA;

    /**
     * Constructor.
     *
     * @param int $attachmentType Helper::ATTACHMENTS_TYPE_SWA | Helper::ATTACHMENTS_TYPE_MTOM
     */
    public function __construct($attachmentType)
    {
        $this->attachmentType = $attachmentType;
    }

    /**
     * Reset all properties to default values.
     */
    public function resetFilter()
    {
        $this->attachmentType = Helper::ATTACHMENTS_TYPE_SWA;
    }

    /**
     * Modify the given request XML.
     *
     * @param \BeSimple\SoapCommon\SoapRequest $request SOAP request
     *
     * @return void
     */
    public function filterRequest(SoapRequest $request)
    {
        // get attachments from request object
        $attachmentsToSend = $request->getAttachments();

        // build mime message if we have attachments
        if (count($attachmentsToSend) > 0) {
            $multipart = new MimeMultiPart();
            $soapPart = new MimePart($request->getContent(), 'text/xml', 'utf-8', MimePart::ENCODING_EIGHT_BIT);
            $soapVersion = $request->getVersion();
            // change content type headers for MTOM with SOAP 1.1
            if ($soapVersion == SOAP_1_1 && $this->attachmentType & Helper::ATTACHMENTS_TYPE_MTOM) {
                $multipart->setHeader('Content-Type', 'type', 'application/xop+xml');
                $multipart->setHeader('Content-Type', 'start-info', 'text/xml');
                $soapPart->setHeader('Content-Type', 'application/xop+xml');
                $soapPart->setHeader('Content-Type', 'type', 'text/xml');
            }
            // change content type headers for SOAP 1.2
            elseif ($soapVersion == SOAP_1_2) {
                $multipart->setHeader('Content-Type', 'type', 'application/soap+xml');
                $soapPart->setHeader('Content-Type', 'application/soap+xml');
            }
            $multipart->addPart($soapPart, true);
            foreach ($attachmentsToSend as $cid => $attachment) {
                $multipart->addPart($attachment, false);
            }
            $request->setContent($multipart->getMimeMessage());

            // TODO
            $headers = $multipart->getHeadersForHttp();
            list(, $contentType) = explode(': ', $headers[0]);

            $request->setContentType($contentType);
        }
    }

    /**
     * Modify the given response XML.
     *
     * @param \BeSimple\SoapCommon\SoapResponse $response SOAP response
     *
     * @return void
     */
    public function filterResponse(SoapResponse $response)
    {
        // array to store attachments
        $attachmentsRecieved = array();

        // check content type if it is a multipart mime message
        $responseContentType = $response->getContentType();
        if (false !== stripos($responseContentType, 'multipart/related')) {
            // parse mime message
            $headers = array(
                'Content-Type' => trim($responseContentType),
            );
            $multipart = MimeParser::parseMimeMessage($response->getContent(), $headers);
            // get soap payload and update SoapResponse object
            $soapPart = $multipart->getPart();
            // convert href -> myhref for external references as PHP throws exception in this case
            // http://svn.php.net/viewvc/php/php-src/branches/PHP_5_4/ext/soap/php_encoding.c?view=markup#l3436
            $content = preg_replace('/href=(?!#)/', 'myhref=', $soapPart->getContent());
            $response->setContent($content);
            $response->setContentType($soapPart->getHeader('Content-Type'));
            // store attachments
            $attachments = $multipart->getParts(false);
            foreach ($attachments as $cid => $attachment) {
                $attachmentsRecieved[$cid] = $attachment;
            }
        }

        // add attachments to response object
        if (count($attachmentsRecieved) > 0) {
            $response->setAttachments($attachmentsRecieved);
        }
    }
}
