<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Dumper;

use BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition;
use BeSimple\SoapBundle\ServiceDefinition\Loader\AnnotationComplexTypeLoader;
use BeSimple\SoapBundle\ServiceDefinition\Strategy\ComplexType;
use BeSimple\SoapBundle\Util\String;

use Zend\Soap\Exception;
use Zend\Soap\Wsdl as BaseWsdl;
use Zend\Soap\Wsdl\ComplexTypeStrategy\ComplexTypeStrategyInterface;
use Zend\Soap\Wsdl\ComplexTypeStrategy\ArrayOfTypeSequence;

class WsdlTypeStrategy implements ComplexTypeStrategyInterface
{
    /**
     * Context WSDL file
     *
     * @var \Zend\Soap\Wsdl|null
     */
    private $context;

    private $loader;
    private $definition;

    private $typeStrategy;
    private $arrayStrategy;

    public function __construct(AnnotationComplexTypeLoader $loader, ServiceDefinition $definition)
    {
        $this->loader     = $loader;
        $this->definition = $definition;
    }

    /**
     * Method accepts the current WSDL context file.
     *
     * @param \Zend\Soap\Wsdl $context
     */
    public function setContext(BaseWsdl $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Create a complex type based on a strategy
     *
     * @param  string $type
     *
     * @return string XSD type
     *
     * @throws \Zend\Soap\WsdlException
     */
    public function addComplexType($type)
    {
        if (!$this->context) {
            throw new \LogicException(sprintf('Cannot add complex type "%s", no context is set for this composite strategy.', $type));
        }

        $strategy = String::endsWith($type, '[]') ? $this->getArrayStrategy() : $this->getTypeStrategy();

        return $strategy->addComplexType($type);
    }

    private function getArrayStrategy()
    {
        if (!$this->arrayStrategy) {
            $this->arrayStrategy = new ArrayOfTypeSequence();
            $this->arrayStrategy->setContext($this->context);
        }

        return $this->arrayStrategy;
    }

    private function getTypeStrategy()
    {
        if (!$this->typeStrategy) {
            $this->typeStrategy = new ComplexType($this->loader, $this->definition);
            $this->typeStrategy->setContext($this->context);
        }

        return $this->typeStrategy;
    }
}