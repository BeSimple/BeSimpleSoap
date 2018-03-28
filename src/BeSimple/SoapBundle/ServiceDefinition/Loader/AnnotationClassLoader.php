<?php

/*
 * This file is part of the BeSimpleSoap.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Loader;

use BeSimple\SoapBundle\ServiceDefinition as Definition;
use BeSimple\SoapBundle\ServiceDefinition\Annotation;
use BeSimple\SoapCommon\Definition\Type\ComplexType;
use BeSimple\SoapCommon\Definition\Type\TypeRepository;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\Loader\Loader;

/**
 * AnnotationClassLoader loads ServiceDefinition from a PHP class and its methods.
 *
 * Based on \Symfony\Component\Routing\Loader\AnnotationClassLoader
 *
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class AnnotationClassLoader extends Loader
{
    protected $reader;

    protected $typeRepository;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Annotations\Reader $reader
     */
    public function __construct(Reader $reader, TypeRepository $typeRepository)
    {
        $this->reader = $reader;
        $this->typeRepository = $typeRepository;
    }

    /**
     * Loads a ServiceDefinition from annotations from a class.
     *
     * @param string $class A class name
     * @param string $type  The resource type
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition A ServiceDefinition instance
     *
     * @throws \InvalidArgumentException When route can't be parsed
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class      = new \ReflectionClass($class);
        $definition = new Definition\Definition($this->typeRepository);

        $sharedHeaders = array();
        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Annotation\Header) {
                $sharedHeaders[$annotation->getValue()] = $this->loadType($annotation->getPhpType());
            }
        }

        foreach ($class->getMethods() as $method) {
            $serviceHeaders   = $sharedHeaders;
            $serviceArguments = array();
            $serviceMethod    =
            $serviceReturn    = null;

            foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                if ($annotation instanceof Annotation\Header) {
                    $serviceHeaders[$annotation->getValue()] = $this->loadType($annotation->getPhpType());
                } elseif ($annotation instanceof Annotation\Param) {
                    $serviceArguments[$annotation->getValue()] = $this->loadType($annotation->getPhpType());
                } elseif ($annotation instanceof Annotation\Method) {
                    if ($serviceMethod) {
                        throw new \LogicException(sprintf('@Soap\Method defined twice for "%s".', $method->getName()));
                    }

                    $serviceMethod = new Definition\Method(
                        $annotation->getValue(),
                        $this->getController($class, $method, $annotation)
                    );
                } elseif ($annotation instanceof Annotation\Result) {
                    if ($serviceReturn) {
                        throw new \LogicException(sprintf('@Soap\Result defined twice for "%s".', $method->getName()));
                    }

                    $serviceReturn = $annotation->getPhpType();
                }
            }

            if (!$serviceMethod && (!empty($serviceArguments) || $serviceReturn)) {
                throw new \LogicException(sprintf('@Soap\Method non-existent for "%s".', $method->getName()));
            }

            if ($serviceMethod) {
                foreach ($serviceHeaders as $name => $type) {
                    $serviceMethod->addHeader($name, $type);
                }

                foreach ($serviceArguments as $name => $type) {
                    $serviceMethod->addInput($name, $type);
                }

                if (!$serviceReturn) {
                    throw new \LogicException(sprintf('@Soap\Result non-existent for "%s".', $method->getName()));
                }

                $serviceMethod->setOutput($this->loadType($serviceReturn));

                $definition->addMethod($serviceMethod);
            }
        }

        return $definition;
    }

    /**
     * @param \ReflectionMethod $method
     * @param \BeSimple\SoapBundle\ServiceDefinition\Annotation\Method $annotation
     *
     * @return string
     */
    private function getController(\ReflectionClass $class, \ReflectionMethod $method, Annotation\Method $annotation)
    {
        if(null !== $annotation->getService()) {
            return $annotation->getService() . ':' . $method->name;
        } else {
            return $class->name . '::' . $method->name;
        }
    }

    private function loadType($phpType)
    {
        if (false !== $arrayOf = $this->typeRepository->getArrayOf($phpType)) {
            $this->loadType($arrayOf);
        }

        if (!$this->typeRepository->hasType($phpType)) {
            $complexTypeResolver = $this->resolve($phpType, 'annotation_complextype');
            if (!$complexTypeResolver) {
                throw new \Exception();
            }

            $loaded = $complexTypeResolver->load($phpType);
            $complexType = new ComplexType($phpType, isset($loaded['alias']) ? $loaded['alias'] : $phpType);
            foreach ($loaded['properties'] as $name => $property) {
                $complexType->add($name, $this->loadType($property->getValue()), $property->isNillable(), $property->isAttribute());
            }

            $this->typeRepository->addComplexType($complexType);
        }

        return $phpType;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource) && (!$type || 'annotation' === $type);
    }

    /**
     * @return null
     */
    public function getResolver()
    {
    }
}
