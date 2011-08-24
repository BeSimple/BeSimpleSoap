<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Loader;

use BeSimple\SoapBundle\ServiceDefinition\ComplexType;
use BeSimple\SoapBundle\Util\Collection;

/**
 * AnnotationComplexTypeLoader loads ServiceDefinition from a PHP class and its methods.
 *
 * Based on \Symfony\Component\Routing\Loader\AnnotationClassLoader
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class AnnotationComplexTypeLoader extends AnnotationClassLoader
{
    private $complexTypeClass = 'BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType';

    /**
     * Loads a ServiceDefinition from annotations from a class.
     *
     * @param string $class A class name
     * @param string $type  The resource type
     *
     * @return ServiceDefinition A ServiceDefinition instance
     *
     * @throws \InvalidArgumentException When route can't be parsed
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class      = new \ReflectionClass($class);
        $collection = new Collection('getName', 'BeSimple\SoapBundle\ServiceDefinition\ComplexType');

        foreach ($class->getProperties() as $property) {
            $complexType = $this->reader->getPropertyAnnotation($property, $this->complexTypeClass);

            if ($complexType) {
                $propertyComplexType = new ComplexType();
                $propertyComplexType->setValue($complexType->getValue());
                $propertyComplexType->setNillable($complexType->isNillable());
                $propertyComplexType->setName($property->getName());
                $collection->add($propertyComplexType);
            }
        }

        return $collection;
    }
}