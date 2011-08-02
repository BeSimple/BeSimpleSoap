Array
=====

Controller
----------

.. code-block:: php

    namespace My\App\Controller;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use BeSimple\SoapBundle\Soap\SoapResponse;
    use Symfony\Component\DependencyInjection\ContainerAware;

    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("isString")
         * @Soap\Param("strings", phpType = "string[]")
         * @Soap\Result(phpType = "boolean")
         */
        public function helloAction(array $strings)
        {
            return new SoapResponse(true);
        }
    }