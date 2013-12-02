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
use BeSimple\SoapCommon\SoapRequest;
use BeSimple\SoapCommon\SoapRequestFilter;

/**
 * XML MIME filter that fixes the namespace of xmime:contentType attribute.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class XmlMimeFilter implements SoapRequestFilter
{
    /**
     * Reset all properties to default values.
     */
    public function resetFilter()
    {
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
        // get \DOMDocument from SOAP request
        $dom = $request->getContentDocument();

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
