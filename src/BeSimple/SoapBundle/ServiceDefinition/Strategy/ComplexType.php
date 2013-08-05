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
    public function addComplexType($classname)
    {
        $classmap = $this->definition->getClassmap();
        if ($classmap->hasByClassname($classname)) {
            return 'tns:'.$classmap->getByClassname($classname);
        }

        if (!$this->loader->supports($classname)) {
            throw new \InvalidArgumentException(sprintf('Cannot add ComplexType "%s" because it is not an object or the class could not be found.', $classname));
        }

        $definitionComplexType = $this->loader->load($classname);
        $classnameAlias        = isset($definitionComplexType['alias']) ? $definitionComplexType['alias'] : $classname;

        $type = $this->getContext()->translateType($classnameAlias);
        $xmlType = 'tns:'.$type;

        // Register type here to avoid recursion
        $classmap->add($type, $classname);
        $this->addXmlDefinition($definitionComplexType, $classname, $type);

        return $xmlType;
    }

    private function addXmlDefinition(array $definitionComplexType, $classname, $type)
    {
        $dom = $this->getContext()->toDomDocument();
        $complexType = $dom->createElement('xsd:complexType');
        $complexType->setAttribute('name', $type);

        $all = $dom->createElement('xsd:all');

        $elements = array();
        foreach ($definitionComplexType['properties'] as $property) {
            $element = $dom->createElement('xsd:element');
            $element->setAttribute('name', $property->getName());
            $element->setAttribute('type', $this->getContext()->getType($property->getValue()));

            if ($property->isNillable()) {
                $element->setAttribute('nillable', 'true');
            }

            $all->appendChild($element);
        }

        $complexType->appendChild($all);
        $this->getContext()->getSchema()->appendChild($complexType);

        $this->definition->addDefinitionComplexType($type, $definitionComplexType);
    }
}
