<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Strategy;

use BeSimple\SoapBundle\ServiceDefinition\Loader\AnnotationComplexTypeLoader;
use Zend\Soap\Wsdl;
use Zend\Soap\Wsdl\ComplexTypeStrategy\AbstractComplexTypeStrategy;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ComplexType extends AbstractComplexTypeStrategy
{
    private $loader;
    private $definition;

    public function __construct(AnnotationComplexTypeLoader $loader, $definition)
    {
        $this->loader     = $loader;
        $this->definition = $definition;
    }

    /**
     * Add a complex type by recursivly using all the class properties fetched via Reflection.
     *
     * @param  string $type Name of the class to be specified
     * @return string XSD Type for the given PHP type
     */
    public function addComplexType($type)
    {
        // Really needed?
        if (null !== $soapType = $this->scanRegisteredTypes($type)) {
            return $soapType;
        }

        $classmap = $this->definition->getClassmap();
        if ($classmap->has($type)) {
            $xmlName = $classmap->get($type);
            $this->addDefinition($type, $xmlName);

            $xmlType = 'tns:'.$xmlName;
        } else {
            if (!$this->loader->supports($type)) {
                throw new \InvalidArgumentException(sprintf('Cannot add a complex type "%s" that is not an object or where class could not be found in "ComplexType" strategy.', $type));
            }

            $xmlName = $this->getContext()->translateType($type);
            $xmlType = 'tns:'.$xmlName;

            // Register type here to avoid recursion
            $classmap->add($type, $xmlName);
            $this->getContext()->addType($type, $soapType);
        }

        $this->addDefinition($type, $xmlName);

        return $xmlType;
    }

    private function addDefinition($type, $xmlName)
    {
        if ($this->definition->hasDefinitionComplexType($type)) {
            return false;
        }

        $dom = $this->getContext()->toDomDocument();
        $complexType = $dom->createElement('xsd:complexType');
        $complexType->setAttribute('name', $xmlName);

        $all = $dom->createElement('xsd:all');

        $elements              = array();
        $definitionComplexType = $this->loader->load($type);
        foreach ($definitionComplexType as $annotationComplexType) {
            $element = $dom->createElement('xsd:element');
            $element->setAttribute('name', $annotationComplexType->getName());
            $element->setAttribute('type', $this->getContext()->getType($annotationComplexType->getValue()));

            if ($annotationComplexType->isNillable()) {
                $element->setAttribute('nillable', 'true');
            }

            $all->appendChild($element);
        }

        $complexType->appendChild($all);
        $this->getContext()->getSchema()->appendChild($complexType);

        $this->definition->addDefinitionComplexType($type, $definitionComplexType);

        return true;
    }
}
