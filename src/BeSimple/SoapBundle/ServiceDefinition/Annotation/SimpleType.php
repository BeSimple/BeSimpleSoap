<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Annotation;

/**
 * @Annotation
 */
class SimpleType extends Configuration
{
    private $name;
    private $value;
    private $nillable = false;
    private $minOccurs = null;
    private $maxOccurs = null;
    /** @var array */
    private $restriction;

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isNillable()
    {
        return $this->nillable;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setNillable($isNillable)
    {
        $this->isNillable = (bool) $isNillable;
    }

    public function getAliasName()
    {
        return 'complextype';
    }

    public function getMinOccurs()
    {
        return $this->minOccurs;
    }

    public function setMinOccurs($minOccurs)
    {
        $this->minOccurs = $minOccurs;
    }

    public function getMaxOccurs()
    {
        return $this->maxOccurs;
    }

    public function setMaxOccurs($maxOccurs)
    {
        $this->maxOccurs = $maxOccurs;
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        return $this->restriction;
    }

    /**
     * @param array $restriction
     */
    public function setRestriction($restriction)
    {
        $this->restriction = $restriction;
    }
}
