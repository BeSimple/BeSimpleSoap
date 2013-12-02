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

use BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition;
use BeSimple\SoapBundle\Util\Assert;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class TypeRepository
{
    const ARRAY_SUFFIX = '[]';

    private $xmlNamespaces  = array();
    private $defaultTypeMap = array();

    public function addXmlNamespace($prefix, $url)
    {
        $this->xmlNamespaces[$prefix] = $url;
    }

    public function getXmlNamespace($prefix)
    {
        return $this->xmlNamespaces[$prefix];
    }

    public function addDefaultTypeMapping($phpType, $xmlType)
    {
        Assert::thatArgumentNotNull('phpType', $phpType);
        Assert::thatArgumentNotNull('xmlType', $xmlType);

        $this->defaultTypeMap[$phpType] = $xmlType;
    }

    public function getXmlTypeMapping($phpType)
    {
        return isset($this->defaultTypeMap[$phpType]) ? $this->defaultTypeMap[$phpType] : null;
    }

    public function fixTypeInformation(ServiceDefinition $definition)
    {
        foreach($definition->getAllTypes() as $type) {
            $phpType = $type->getPhpType();
            $xmlType = $type->getXmlType();

            if (null === $phpType) {
                throw new \InvalidArgumentException();
            }

            if (null === $xmlType) {
                $xmlType = $this->getXmlTypeMapping($phpType);
            }

            $type->setXmlType($xmlType);
        }
    }
}
