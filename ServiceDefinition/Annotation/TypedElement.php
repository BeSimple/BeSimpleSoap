<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Annotation;

abstract class TypedElement
{
    private $phpType;
    private $xmlType;

    public function __construct($values)
    {
        foreach(array('type', 'phpType') as $key)
        {
            if(isset($values[$key]))
            {
                $this->phpType =  $values[$key];
            }
        }

        $this->xmlType = isset($values['xmlType']) ? $values['xmlType'] : null;
    }

    public function getPhpType()
    {
        return $this->phpType;
    }

    public function getXmlType()
    {
        return $this->xmlType;
    }
}