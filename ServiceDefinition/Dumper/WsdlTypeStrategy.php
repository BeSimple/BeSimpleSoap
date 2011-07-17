<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Dumper;

use Bundle\WebServiceBundle\Util\String;

use Zend\Soap\Exception;
use Zend\Soap\Wsdl;
use Zend\Soap\Wsdl\Strategy;
use Zend\Soap\Wsdl\Strategy\ArrayOfTypeSequence;
use Zend\Soap\Wsdl\Strategy\DefaultComplexType;

class WsdlTypeStrategy implements Strategy
{
    /**
     * Context WSDL file
     *
     * @var \Zend\Soap\Wsdl|null
     */
    private $context;

    private $typeStrategy;
    private $arrayStrategy;

    public function __construct()
    {
        $this->typeStrategy  = new DefaultComplexType();
        $this->arrayStrategy = new ArrayOfTypeSequence();
    }

    /**
     * Method accepts the current WSDL context file.
     *
     * @param \Zend\Soap\Wsdl $context
     */
    public function setContext(Wsdl $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Create a complex type based on a strategy
     *
     * @throws \Zend\Soap\WsdlException
     * @param  string $type
     * @return string XSD type
     */
    public function addComplexType($type)
    {
        if (!$this->context) {
            throw new \LogicException(sprintf('Cannot add complex type "%s", no context is set for this composite strategy.', $type));
        }

        $strategy = String::endsWith($type, '[]') ? $this->arrayStrategy : $this->typeStrategy;
        $strategy->setContext($this->context);

        return $strategy->addComplexType($type);
    }
}