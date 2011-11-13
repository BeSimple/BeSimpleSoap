Array
=====

Controller
----------

.. code-block:: php

    namespace My\App\Controller;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use Symfony\Component\DependencyInjection\ContainerAware;

    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("hello")
         * @Soap\Param("names", phpType = "string[]")
         * @Soap\Result(phpType = "string")
         */
        public function helloAction(array $names)
        {
            return $this->container->get('besimple.soap.response')->setReturnValue("Hello ".implode(', ', $names));
        }
    }