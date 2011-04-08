<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Loader;

use Doctrine\Common\Annotations\AnnotationReader as BaseAnnotationReader;

/**
 * AnnotationReader.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class AnnotationReader extends BaseAnnotationReader
{
    public function getMethodAnnotation(\ReflectionMethod $method, $type)
    {
        $annotation = parent::getMethodAnnotation($method, $type);
        
        if($annotation !== null && count($annotation) > 1)
        {
            throw new \LogicException(sprintf("There is more than one annotation of type '%s'!", $type));
        }
        
        return $annotation !== null ? $annotation[0] : null;
    }
    
    public function getMethodAnnotations(\ReflectionMethod $method, $type = null)
    {
        $annotations = parent::getMethodAnnotations($method);
        
        return $type !== null && isset($annotations[$type]) ? $annotations[$type] : $annotations;
    }
}