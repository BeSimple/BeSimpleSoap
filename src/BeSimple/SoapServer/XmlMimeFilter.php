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

namespace BeSimple\SoapServer;

use BeSimple\SoapCommon\FilterHelper;
use BeSimple\SoapCommon\Helper;
use BeSimple\SoapCommon\SoapResponse;
use BeSimple\SoapCommon\SoapResponseFilter;

/**
 * XML MIME filter that fixes the namespace of xmime:contentType attribute.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class XmlMimeFilter implements SoapResponseFilter
{
    /**
     * Reset all properties to default values.
     */
    public function resetFilter()
    {
    }

    /**
     * Modify the given response XML.
     *
     * @param \BeSimple\SoapCommon\SoapResponse $response SOAP request
     *
     * @return void
     */
    public function filterResponse(SoapResponse $response)
    {
        // get \DOMDocument from SOAP request
        $dom = $response->getContentDocument();

        // create FilterHelper
        $filterHelper = new FilterHelper($dom);

        // add the neccessary namespaces
        $filterHelper->addNamespace(Helper::PFX_XMLMIME, Helper::NS_XMLMIME);

        // get xsd:base64binary elements
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('XOP', Helper::NS_XOP);
        $query = '//XOP:Include/..';
        $nodes = $xpath->query($query);

        // exchange attributes
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                if ($node->hasAttribute('contentType')) {
                    $contentType = $node->getAttribute('contentType');
                    $node->removeAttribute('contentType');
                    $filterHelper->setAttribute($node, Helper::NS_XMLMIME, 'contentType', $contentType);
                }
            }
        }

    }
}
