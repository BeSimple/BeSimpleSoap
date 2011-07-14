<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */


namespace Bundle\WebServiceBundle\ServiceDefinition\Loader;



use Bundle\WebServiceBundle\ServiceDefinition\ServiceDefinition;
use Bundle\WebServiceBundle\ServiceDefinition\Method;
use Bundle\WebServiceBundle\ServiceDefinition\Argument;
use Bundle\WebServiceBundle\ServiceDefinition\Type;

use Bundle\WebServiceBundle\ServiceDefinition\Annotation\Method as MethodAnnotation;

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
    private $wsMethodAnnotationClass = 'Bundle\\WebServiceBundle\\ServiceDefinition\\Annotation\\Method';
    private $wsParamAnnotationClass = 'Bundle\\WebServiceBundle\\ServiceDefinition\\Annotation\\Param';
    private $wsResultAnnotationClass = 'Bundle\\WebServiceBundle\\ServiceDefinition\\Annotation\\Result';

    protected $reader;

    /**
     * Constructor.
     *
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
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

        $class = new \ReflectionClass($class);

        $definition = new ServiceDefinition();

        foreach ($class->getMethods() as $method) {
            $wsMethodAnnot = $this->reader->getMethodAnnotation($method, $this->wsMethodAnnotationClass);

            if($wsMethodAnnot !== null) {
                $wsParamAnnots = $this->reader->getMethodAnnotations($method, $this->wsParamAnnotationClass);
                $wsResultAnnot = $this->reader->getMethodAnnotation($method, $this->wsResultAnnotationClass);

                $serviceMethod = new Method();
                $serviceMethod->setName($wsMethodAnnot->getName($method->getName()));
                $serviceMethod->setController($this->getController($method, $wsMethodAnnot));

                foreach($wsParamAnnots as $wsParamAnnot) {
                    $serviceArgument = new Argument();
                    $serviceArgument->setName($wsParamAnnot->getName());
                    $serviceArgument->setType(new Type($wsParamAnnot->getPhpType(), $wsParamAnnot->getXmlType()));

                    $serviceMethod->getArguments()->add($serviceArgument);
                }

                if($wsResultAnnot !== null) {
                    $serviceMethod->setReturn(new Type($wsResultAnnot->getPhpType(), $wsResultAnnot->getXmlType()));
                }

                $definition->getMethods()->add($serviceMethod);
            }
        }

        return $definition;
    }

    private function getController(\ReflectionMethod $method, MethodAnnotation $annotation)
    {
        if($annotation->getService() !== null) {
            return $annotation->getService() . ':' . $method->name;
        } else {
            return $method->class . '::' . $method->name;
        }
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