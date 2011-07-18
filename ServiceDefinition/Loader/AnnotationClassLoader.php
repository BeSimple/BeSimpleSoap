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

use BeSimple\SoapBundle\ServiceDefinition\Argument;
use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapBundle\ServiceDefinition\Type;
use BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition;
use BeSimple\SoapBundle\ServiceDefinition\Annotation\Method as MethodAnnotation;
use BeSimple\SoapBundle\ServiceDefinition\Annotation\Param as ParamAnnotation;
use BeSimple\SoapBundle\ServiceDefinition\Annotation\Result as ResultAnnotation;

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
    private $methodAnnotationClass = 'BeSimple\\SoapBundle\\ServiceDefinition\\Annotation\\Method';
    private $paramAnnotationClass  = 'BeSimple\\SoapBundle\\ServiceDefinition\\Annotation\\Param';
    private $resultAnnotationClass = 'BeSimple\\SoapBundle\\ServiceDefinition\\Annotation\\Result';

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
        $definition = new ServiceDefinition();

        foreach ($class->getMethods() as $method) {
            $serviceArguments = array();
            $serviceMethod    =
            $serviceReturn    = null;

            foreach ($this->reader->getMethodAnnotations($method) as $i => $annotation) {
                if ($annotation instanceof ParamAnnotation) {
                    $serviceArguments[] = new Argument(
                        $annotation->getValue(),
                        $this->getArgumentType($method, $annotation)
                    );
                } elseif ($annotation instanceof MethodAnnotation) {
                    if ($serviceMethod) {
                        throw new \LogicException(sprintf('@Method defined twice for "%s".', $method->getName()));
                    }

                    $serviceMethod = new Method(
                        $annotation->getValue(),
                        $this->getController($method, $annotation)
                    );
                } elseif ($annotation instanceof ResultAnnotation) {
                    if ($serviceReturn) {
                        throw new \LogicException(sprintf('@Result defined twice for "%s".', $method->getName()));
                    }

                    $serviceReturn = new Type($annotation->getPhpType(), $annotation->getXmlType());
                }
            }

            if (!$serviceMethod && (!empty($serviceArguments) || $serviceReturn)) {
                throw new \LogicException(sprintf('@Method non-existent for "%s".', $method->getName()));
            }

            if ($serviceMethod) {
                $serviceMethod->setArguments($serviceArguments);

                if ($serviceReturn) {
                    $serviceMethod->setReturn($serviceReturn);
                }

                $definition->getMethods()->add($serviceMethod);
            }
        }

        return $definition;
    }

    private function getController(\ReflectionMethod $method, MethodAnnotation $annotation)
    {
        if(null !== $annotation->getService()) {
            return $annotation->getService() . ':' . $method->name;
        } else {
            return $method->class . '::' . $method->name;
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @param ParamAnnotation   $annotation
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\Type
     */
    private function getArgumentType(\ReflectionMethod $method, ParamAnnotation $annotation)
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

        return new Type($phpType, $xmlType);
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
