<?php

/*
 * This file is part of the BeSimpleSoap.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Definition\Type;

use BeSimple\SoapCommon\Classmap;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class TypeRepository
{
    const ARRAY_SUFFIX = '[]';

    protected $xmlNamespaces = array();
    protected $types = array();

    protected $classmap;

    public function __construct(Classmap $classmap = null)
    {
        $this->classmap = $classmap;
    }

    public function getXmlNamespaces()
    {
        return $this->xmlNamespaces;
    }
    public function getXmlNamespace($prefix)
    {
        return $this->xmlNamespaces[$prefix];
    }

    public function addXmlNamespace($prefix, $url)
    {
        $this->xmlNamespaces[$prefix] = $url;
    }

    public function getComplexTypes()
    {
        $types = array();
        foreach ($this->types as $type) {
            if ($type instanceof ComplexType) {
                $types[] = $type;
            }
        }

        return $types;
    }

    public function getType($phpType)
    {
        if (!$this->hasType($phpType)) {
            throw new \Exception();
        }

        return $this->types[$phpType];
    }

    public function addType($phpType, $xmlType)
    {
        return $this->types[$phpType] = $xmlType;
    }

    public function addComplexType(ComplexType $type)
    {
        $phpType = $type->getPhpType();

        $this->types[$phpType] = $type;
        $this->addClassmap($type->getXmlType(), $phpType);
    }

    public function hasType($type)
    {
        if ($type instanceof TypeInterface) {
            $phpType = $type->getPhpType();

            return !(!$this->hasType($phpType) || $type !== $this->getType($phpType));
        }

        if (isset($this->types[$type])) {
            return true;
        }

        if (false !== $arrayOf = $this->getArrayOf($type)) {
            if ($this->hasType($arrayOf)) {
                $xmlTypeOf = null;
                $arrayOfType = $this->getType($arrayOf);
                if ($arrayOfType instanceof ComplexType) {
                    $xmlTypeOf = $arrayOfType->getXmlType();
                }

                $arrayType = new ArrayOfType($type, $arrayOf, $xmlTypeOf);
                $this->addType($type, $arrayType);

                return true;
            }
        }

        return false;
    }

    public function getArrayOf($arrayType)
    {
        if (!preg_match('#(.*)'.preg_quote(static::ARRAY_SUFFIX, '#').'$#', $arrayType, $match)) {
            return false;
        }

        return $match[1];
    }

    public function getClassmap()
    {
        return $this->classmap;
    }

    protected function addClassmap($xmlType, $phpType)
    {
        if (!$this->classmap) {
            return;
        }

        $this->classmap->add($xmlType, $phpType);
    }
}
