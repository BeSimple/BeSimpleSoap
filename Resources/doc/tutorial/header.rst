Header
======

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
         * @Soap\Header("api_key", phpType = "string")
         * @Soap\Param("names", phpType = "string[]")
         * @Soap\Result(phpType = "string")
         */
        public function helloAction(array $names)
        {
            $soapHeaders = $this->container->get('request')->getSoapHeaders();

            // You can use '1234' !== (string) $soapHeaders->get('api_key')
            if (!$soapHeaders->has('api_key') || '1234' !== $soapHeaders->get('api_key')->getData()) {
                throw new \SoapFault("INVALID_API_KEY", "The api_key is invalid.");
            }

            return $this->container->get('besimple.soap.response')->setReturnValue("Hello ".implode(', ', $names));
        }
    }