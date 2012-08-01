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

use BeSimple\SoapBundle\ServiceDefinition as Definition;
use BeSimple\SoapBundle\ServiceDefinition\Annotation;

use Doctrine\Common\Annotations\Reader;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;

/**
 * AnnotationClassLoader loads ServiceDefinition from a PHP class and its methods.
 *
 * Based on \Symfony\Component\Routing\Loader\AnnotationClassLoader
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class AnnotationClassLoader implements LoaderInterface
{
    protected $reader;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Annotations\Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
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
        $definition = new Definition\ServiceDefinition();

        $serviceMethodHeaders = array();
        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Annotation\Header) {
                $serviceMethodHeaders[$annotation->getValue()] = $annotation;
            }
        }

        foreach ($class->getMethods() as $method) {
            $serviceArguments =
            $serviceHeaders   = array();
            $serviceMethod    =
            $serviceReturn    = null;

            foreach ($serviceMethodHeaders as $annotation) {
                $serviceHeaders[$annotation->getValue()] = new Definition\Header(
                    $annotation->getValue(),
                    $this->getArgumentType($method, $annotation)
                );
            }

            foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                if ($annotation instanceof Annotation\Header) {
                    $serviceHeaders[$annotation->getValue()] = new Definition\Header(
                        $annotation->getValue(),
                        $this->getArgumentType($method, $annotation)
                    );
                } elseif ($annotation instanceof Annotation\Param) {
                    $serviceArguments[] = new Definition\Argument(
                        $annotation->getValue(),
                        $this->getArgumentType($method, $annotation)
                    );
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

                    $serviceReturn = new Definition\Type($annotation->getPhpType(), $annotation->getXmlType());
                }
            }

            if (!$serviceMethod && (!empty($serviceArguments) || $serviceReturn)) {
                throw new \LogicException(sprintf('@Soap\Method non-existent for "%s".', $method->getName()));
            }

            if ($serviceMethod) {
                $serviceMethod->setArguments($serviceArguments);
                $serviceMethod->setHeaders($serviceHeaders);

                if (!$serviceReturn) {
                    throw new \LogicException(sprintf('@Soap\Result non-existent for "%s".', $method->getName()));
                }

                $serviceMethod->setReturn($serviceReturn);

                $definition->getMethods()->add($serviceMethod);
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

    /**
     * @param \ReflectionMethod $method
     * @param \BeSimple\SoapBundle\ServiceDefinition\Annotation\Param $annotation
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\Type
     */
    private function getArgumentType(\ReflectionMethod $method, Annotation\Param $annotation)
    {
        $phpType = $annotation->getPhpType();
        $xmlType = $annotation->getXmlType();

        if (null === $phpType) {
            foreach ($method->getParameters() as $param) {
                if ($param->name === $annotation->getName()) {
                    $phpType = $param->getClass()->name;

                    break;
                }
            }
        }

        return new Definition\Type($phpType, $xmlType);
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
     * Sets the loader resolver.
     *
     * @param LoaderResolver $resolver A LoaderResolver instance
     */
    public function setResolver(LoaderResolver $resolver)
    {
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolver A LoaderResolver instance
     */
    public function getResolver()
    {
    }
}
