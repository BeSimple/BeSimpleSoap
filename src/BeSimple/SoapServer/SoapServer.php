<?php

/*
 * This file is part of the BeSimpleSoapServer.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapServer;

use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\Converter\MtomTypeConverter;
use BeSimple\SoapCommon\Converter\SwaTypeConverter;

/**
 * Extended SoapServer that allows adding filters for SwA, MTOM, ... .
 *
 * @author Andreas Schamberger <mail@andreass.net>
 * @author Christian Kerl <christian-kerl@web.de>
 */
class SoapServer extends \SoapServer
{
    /**
     * Soap version.
     *
     * @var int
     */
    protected $soapVersion = SOAP_1_1;

    /**
     * Soap kernel.
     *
     * @var \BeSimple\SoapServer\SoapKernel
     */
    protected $soapKernel = null;

    /**
     * Constructor.
     *
     * @param string               $wsdl    WSDL file
     * @param array(string=>mixed) $options Options array
     */
    public function __construct($wsdl, array $options = array())
    {
        // store SOAP version
        if (isset($options['soap_version'])) {
            $this->soapVersion = $options['soap_version'];
        }
        // create soap kernel instance
        $this->soapKernel = new SoapKernel();
        // set up type converter and mime filter
        $this->configureMime($options);
        // we want the exceptions option to be set
        $options['exceptions'] = true;
        parent::__construct($wsdl, $options);
    }

    /**
     * Custom handle method to be able to modify the SOAP messages.
     *
     * @param string $request Request string
     */
    public function handle($request = null)
    {
        // wrap request data in SoapRequest object
        $soapRequest = SoapRequest::create($request, $this->soapVersion);

        // handle actual SOAP request
        try {
            $soapResponse = $this->handle2($soapRequest);
        } catch (\SoapFault $fault) {
            // issue an error to the client
            $this->fault($fault->faultcode, $fault->faultstring);
        }

        // send SOAP response to client
        $soapResponse->send();
    }

    /**
     * Runs the currently registered request filters on the request, calls the
     * necessary functions (through the parent's class handle()) and runs the
     * response filters.
     *
     * @param SoapRequest $soapRequest SOAP request object
     *
     * @return SoapResponse
     */
    public function handle2(SoapRequest $soapRequest)
    {
        // run SoapKernel on SoapRequest
        $this->soapKernel->filterRequest($soapRequest);

        // call parent \SoapServer->handle() and buffer output
        ob_start();
        parent::handle($soapRequest->getContent());
        $response = ob_get_clean();

        // Remove headers added by SoapServer::handle() method
        header_remove('Content-Length');
        header_remove('Content-Type');

        // wrap response data in SoapResponse object
        $soapResponse = SoapResponse::create(
            $response,
            $soapRequest->getLocation(),
            $soapRequest->getAction(),
            $soapRequest->getVersion()
        );

        // run SoapKernel on SoapResponse
        $this->soapKernel->filterResponse($soapResponse);

        return $soapResponse;
    }

    /**
     * Get SoapKernel instance.
     *
     * @return \BeSimple\SoapServer\SoapKernel
     */
    public function getSoapKernel()
    {
        return $this->soapKernel;
    }

    /**
     * Configure filter and type converter for SwA/MTOM.
     *
     * @param array &$options SOAP constructor options array.
     *
     * @return void
     */
    private function configureMime(array &$options)
    {
        if (isset($options['attachment_type']) && Helper::ATTACHMENTS_TYPE_BASE64 !== $options['attachment_type']) {
            // register mime filter in SoapKernel
            $mimeFilter = new MimeFilter($options['attachment_type']);
            $this->soapKernel->registerFilter($mimeFilter);
            // configure type converter
            if (Helper::ATTACHMENTS_TYPE_SWA === $options['attachment_type']) {
                $converter = new SwaTypeConverter();
                $converter->setKernel($this->soapKernel);
            } elseif (Helper::ATTACHMENTS_TYPE_MTOM === $options['attachment_type']) {
                $xmlMimeFilter = new XmlMimeFilter($options['attachment_type']);
                $this->soapKernel->registerFilter($xmlMimeFilter);
                $converter = new MtomTypeConverter();
                $converter->setKernel($this->soapKernel);
            }
            // configure typemap
            if (!isset($options['typemap'])) {
                $options['typemap'] = array();
            }
            $options['typemap'][] = array(
                'type_name' => $converter->getTypeName(),
                'type_ns'   => $converter->getTypeNamespace(),
                'from_xml'  => function($input) use ($converter) {
                    return $converter->convertXmlToPhp($input);
                },
                'to_xml'    => function($input) use ($converter) {
                    return $converter->convertPhpToXml($input);
                },
            );
        }
    }
}
