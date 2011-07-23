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

use BeSimple\SoapBundle\ServiceDefinition\Annotation\ComplexType;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\PropertyComplexType;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\MethodComplexType;
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
    private $propertyComplexTypeClass = 'BeSimple\SoapBundle\ServiceDefinition\Annotation\PropertyComplexType';
    private $methodComplexTypeClass   = 'BeSimple\SoapBundle\ServiceDefinition\Annotation\MethodComplexType';

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
        $collection = new Collection('getName');

        foreach ($class->getProperties() as $property) {
            if ($property->isPublic()) {
                $complexType = $this->reader->getPropertyAnnotation($property, $this->propertyComplexTypeClass);

                if ($complexType) {
                    $propertyComplexType = new PropertyComplexType();
                    $propertyComplexType->setValue($complexType->getValue());
                    $propertyComplexType->setNillable($complexType->isNillable());

                    if (!$complexType->getName()) {
                        $propertyComplexType->setName($property->getName());
                    } else {
                        $propertyComplexType->setName($complexType->getName());
                        $propertyComplexType->setOriginalName($property->getName());
                    }

                    $collection->add($propertyComplexType);
                }
            }
        }

        foreach ($class->getMethods() as $method) {
            if ($method->isPublic()) {
                $complexType = $this->reader->getMethodAnnotation($method, $this->methodComplexTypeClass);

                if ($complexType) {
                    $methodComplexType = new MethodComplexType();
                    $methodComplexType->setValue($complexType->getValue());
                    $methodComplexType->setSetter($complexType->getSetter());
                    $methodComplexType->setNillable($complexType->isNillable());

                    if (!$complexType->getName()) {
                        $methodComplexType->setName($property->getName());
                    } else {
                        $methodComplexType->setName($complexType->getName());
                        $methodComplexType->setOriginalName($method->getName());
                    }

                    $collection->add($methodComplexType);
                }
            }
        }

        return $collection;
    }
}