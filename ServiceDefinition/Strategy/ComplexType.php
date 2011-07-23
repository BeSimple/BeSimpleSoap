<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Strategy;

use BeSimple\SoapBundle\ServiceDefinition\Loader\AnnotationComplexTypeLoader;

use Zend\Soap\Wsdl;
use Zend\Soap\Wsdl\Strategy\AbstractStrategy;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ComplexType extends AbstractStrategy
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
        if (null !== $soapType = $this->scanRegisteredTypes($type)) {
            return $soapType;
        }

        if (!$this->loader->supports($type)) {
            throw new \InvalidArgumentException(sprintf('Cannot add a complex type "%s" that is not an object or where class could not be found in "ComplexType" strategy.', $type));
        }

        $dom   = $this->getContext()->toDomDocument();

        $soapTypeName = Wsdl::translateType($type);
        $soapType     = 'tns:'.$soapTypeName;

        // Register type here to avoid recursion
        $this->getContext()->addType($type, $soapType);

        $complexType = $dom->createElement('xsd:complexType');
        $complexType->setAttribute('name', $soapTypeName);

        $all = $dom->createElement('xsd:all');

        $definitionComplexType = $this->loader->load($type);
        $this->definition->addDefinitionComplexType($type, $definitionComplexType);

        foreach ($definitionComplexType as $annotationComplexType) {
            $element = $dom->createElement('xsd:element');
            $element->setAttribute('name', $propertyName = $annotationComplexType->getName());
            $element->setAttribute('type', $this->getContext()->getType(trim($annotationComplexType->getValue())));

            if ($annotationComplexType->isNillable()) {
                $element->setAttribute('nillable', 'true');
            }

            $all->appendChild($element);
        }

        $complexType->appendChild($all);
        $this->getContext()->getSchema()->appendChild($complexType);

        return $soapType;
    }
}