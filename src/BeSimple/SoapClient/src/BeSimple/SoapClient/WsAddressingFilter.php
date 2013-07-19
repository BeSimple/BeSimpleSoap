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

use BeSimple\SoapCommon\FilterHelper;
use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\SoapRequest as CommonSoapRequest;
use BeSimple\SoapCommon\SoapRequestFilter;
use BeSimple\SoapCommon\SoapResponse as CommonSoapResponse;
use BeSimple\SoapCommon\SoapResponseFilter;

/**
 * This plugin implements a subset of the following standards:
 *  * Web Services Addressing 1.0 - Core
 *      http://www.w3.org/TR/2006/REC-ws-addr-core
 *  * Web Services Addressing 1.0 - SOAP Binding
 *      http://www.w3.org/TR/ws-addr-soap
 *
 * Per default this plugin uses the SoapClient's $action and $location values
 * for wsa:Action and wsa:To. Therefore the only REQUIRED property 'wsa:Action'
 * is always set automatically.
 *
 * Limitation: wsa:From, wsa:FaultTo and wsa:ReplyTo only support the
 * wsa:Address element of the endpoint reference at the moment.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class WsAddressingFilter implements SoapRequestFilter, SoapResponseFilter
{
    /**
     * (2.1) Endpoint reference (EPR) anonymous default address.
     *
     * Some endpoints cannot be located with a meaningful IRI; this URI is used
     * to allow such endpoints to send and receive messages. The precise meaning
     * of this URI is defined by the binding of Addressing to a specific
     * protocol and/or the context in which the EPR is used.
     *
     * @see http://www.w3.org/TR/2006/REC-ws-addr-core-20060509/#predefaddr
     */
    const ENDPOINT_REFERENCE_ANONYMOUS = 'http://www.w3.org/2005/08/addressing/anonymous';

    /**
     * (2.1) Endpoint reference (EPR) address for discarting messages.
     *
     * Messages sent to EPRs whose [address] is this value MUST be discarded
     * (i.e. not sent). This URI is typically used in EPRs that designate a
     * reply or fault endpoint (see section 3.1 Abstract Property Definitions)
     * to indicate that no reply or fault message should be sent.
     *
     * @see http://www.w3.org/TR/2006/REC-ws-addr-core-20060509/#predefaddr
     */
    const ENDPOINT_REFERENCE_NONE = 'http://www.w3.org/2005/08/addressing/none';

    /**
     * (3.1) Predefined value for reply.
     *
     * Indicates that this is a reply to the message identified by the [message id] IRI.
     *
     * see http://www.w3.org/TR/2006/REC-ws-addr-core-20060509/#predefrels
     */
    const RELATIONSHIP_TYPE_REPLY = 'http://www.w3.org/2005/08/addressing/reply';

    /**
     * FaultTo.
     *
     * @var string
     */
    protected $faultTo;

    /**
     * From.
     *
     * @var string
     */
    protected $from;

    /**
     * MessageId.
     *
     * @var string
     */
    protected $messageId;

    /**
     * List of reference parameters associated with this soap message.
     *
     * @var unknown_type
     */
    protected $referenceParametersSet = array();

    /**
     * List of reference parameters recieved with this soap message.
     *
     * @var unknown_type
     */
    protected $referenceParametersRecieved = array();

    /**
     * RelatesTo.
     *
     * @var string
     */
    protected $relatesTo;

    /**
     * RelatesTo@RelationshipType.
     *
     * @var string
     */
    protected $relatesToRelationshipType;

    /**
     * ReplyTo.
     *
     * @var string
     */
    protected $replyTo;

    /**
     * Add additional reference parameters
     *
     * @param string $ns        Namespace URI
     * @param string $pfx       Namespace prefix
     * @param string $parameter Parameter name
     * @param string $value     Parameter value
     *
     * @return void
     */
    public function addReferenceParameter($ns, $pfx, $parameter, $value)
    {
        $this->referenceParametersSet[] = array(
            'ns' => $ns,
            'pfx' => $pfx,
            'parameter' => $parameter,
            'value' => $value,
        );
    }

    /**
     * Get additional reference parameters.
     *
     * @param string $ns        Namespace URI
     * @param string $parameter Parameter name
     *
     * @return string|null
     */
    public function getReferenceParameter($ns, $parameter)
    {
        if (isset($this->referenceParametersRecieved[$ns][$parameter])) {

            return $this->referenceParametersRecieved[$ns][$parameter];
        }

        return null;
    }

    /**
     * Reset all properties to default values.
     */
    public function resetFilter()
    {
        $this->faultTo                     = null;
        $this->from                        = null;
        $this->messageId                   = null;
        $this->referenceParametersRecieved = array();
        $this->referenceParametersSet      = array();
        $this->relatesTo                   = null;
        $this->relatesToRelationshipType   = null;
        $this->replyTo                     = null;
    }

    /**
     * Set FaultTo address of type xs:anyURI.
     *
     * @param string $faultTo xs:anyURI
     *
     * @return void
     */
    public function setFaultTo($faultTo)
    {
        $this->faultTo = $faultTo;
    }

    /**
     * Set From address of type xs:anyURI.
     *
     * @param string $from xs:anyURI
     *
     * @return void
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Set MessageId of type xs:anyURI.
     * Default: UUID v4 e.g. 'uuid:550e8400-e29b-11d4-a716-446655440000'
     *
     * @param string $messageId xs:anyURI
     *
     * @return void
     */
    public function setMessageId($messageId = null)
    {
        if (null === $messageId) {
            $messageId = 'uuid:' . Helper::generateUUID();
        }
        $this->messageId = $messageId;
    }

    /**
     * Set RelatesTo of type xs:anyURI with the optional relationType
     * (of type xs:anyURI).
     *
     * @param string $relatesTo    xs:anyURI
     * @param string $relationType xs:anyURI
     *
     * @return void
     */
    public function setRelatesTo($relatesTo, $relationType = null)
    {
        $this->relatesTo = $relatesTo;
        if (null !== $relationType && $relationType != self::RELATIONSHIP_TYPE_REPLY) {
            $this->relatesToRelationshipType = $relationType;
        }
    }

    /**
     * Set ReplyTo address of type xs:anyURI
     * Default: self::ENDPOINT_REFERENCE_ANONYMOUS
     *
     * @param string $replyTo xs:anyURI
     *
     * @return void
     */
    public function setReplyTo($replyTo = null)
    {
        if (null === $replyTo) {
            $replyTo = self::ENDPOINT_REFERENCE_ANONYMOUS;
        }
        $this->replyTo = $replyTo;
    }

    /**
     * Modify the given request XML.
     *
     * @param \BeSimple\SoapCommon\SoapRequest $request SOAP request
     *
     * @return void
     */
    public function filterRequest(CommonSoapRequest $request)
    {
        // get \DOMDocument from SOAP request
        $dom = $request->getContentDocument();

        // create FilterHelper
        $filterHelper = new FilterHelper($dom);

        // add the neccessary namespaces
        $filterHelper->addNamespace(Helper::PFX_WSA, Helper::NS_WSA);

        $action = $filterHelper->createElement(Helper::NS_WSA, 'Action', $request->getAction());
        $filterHelper->addHeaderElement($action);

        $to = $filterHelper->createElement(Helper::NS_WSA, 'To', $request->getLocation());
        $filterHelper->addHeaderElement($to);

        if (null !== $this->faultTo) {
            $faultTo = $filterHelper->createElement(Helper::NS_WSA, 'FaultTo');
            $filterHelper->addHeaderElement($faultTo);

            $address = $filterHelper->createElement(Helper::NS_WSA, 'Address', $this->faultTo);
            $faultTo->appendChild($address);
        }

        if (null !== $this->from) {
            $from = $filterHelper->createElement(Helper::NS_WSA, 'From');
            $filterHelper->addHeaderElement($from);

            $address = $filterHelper->createElement(Helper::NS_WSA, 'Address', $this->from);
            $from->appendChild($address);
        }

        if (null !== $this->messageId) {
            $messageId = $filterHelper->createElement(Helper::NS_WSA, 'MessageID', $this->messageId);
            $filterHelper->addHeaderElement($messageId);
        }

        if (null !== $this->relatesTo) {
            $relatesTo = $filterHelper->createElement(Helper::NS_WSA, 'RelatesTo', $this->relatesTo);
            if (null !== $this->relatesToRelationshipType) {
                $filterHelper->setAttribute($relatesTo, Helper::NS_WSA, 'RelationshipType', $this->relatesToRelationshipType);
            }
            $filterHelper->addHeaderElement($relatesTo);
        }

        if (null !== $this->replyTo) {
            $replyTo = $filterHelper->createElement(Helper::NS_WSA, 'ReplyTo');
            $filterHelper->addHeaderElement($replyTo);

            $address = $filterHelper->createElement(Helper::NS_WSA, 'Address', $this->replyTo);
            $replyTo->appendChild($address);
        }

        foreach ($this->referenceParametersSet as $rp) {
            $filterHelper->addNamespace($rp['pfx'], $rp['ns']);
            $parameter = $filterHelper->createElement($rp['ns'], $rp['parameter'], $rp['value']);
            $filterHelper->setAttribute($parameter, Helper::NS_WSA, 'IsReferenceParameter', 'true');
            $filterHelper->addHeaderElement($parameter);
        }
    }

    /**
     * Modify the given response XML.
     *
     * @param \BeSimple\SoapCommon\SoapResponse $response SOAP response
     *
     * @return void
     */
    public function filterResponse(CommonSoapResponse $response)
    {
        // get \DOMDocument from SOAP response
        $dom = $response->getContentDocument();

        $this->referenceParametersRecieved = array();
        $referenceParameters = $dom->getElementsByTagNameNS(Helper::NS_WSA, 'ReferenceParameters')->item(0);
        if (null !== $referenceParameters) {
            foreach ($referenceParameters->childNodes as $childNode) {
                if (!isset($this->referenceParametersRecieved[$childNode->namespaceURI])) {
                    $this->referenceParametersRecieved[$childNode->namespaceURI] = array();
                }
                $this->referenceParametersRecieved[$childNode->namespaceURI][$childNode->localName] = $childNode->nodeValue;
            }
        }
    }
}