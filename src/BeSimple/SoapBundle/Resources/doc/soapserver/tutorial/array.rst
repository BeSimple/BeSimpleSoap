Array
=====

Controller
----------

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use Symfony\Component\DependencyInjection\ContainerAwareInterface;
    use Symfony\Component\DependencyInjection\ContainerAwareTrait;

    class DemoController  implements ContainerAwareInterface
    {
        use ContainerAwareTrait;

        /**
         * @Soap\Method("hello")
         * @Soap\Param("names", phpType = "string[]")
         * @Soap\Result(phpType = "string")
         */
        public function helloAction(array $names)
        {
            return "Hello ".implode(', ', $names);
        }
    }
