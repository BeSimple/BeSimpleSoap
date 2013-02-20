Header
======

Controller
----------

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

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

            return "Hello ".implode(', ', $names);
        }
    }

Global header
-------------

If you want use a header for all actions of your controller you can declare the header like this:

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use Symfony\Component\DependencyInjection\ContainerAware;
    use Symfony\Component\DependencyInjection\ContainerInterface;

    /**
     * @Soap\Header("api_key", phpType = "string")
     */
    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("hello")
         * @Soap\Param("names", phpType = "string[]")
         * @Soap\Result(phpType = "string")
         */
        public function helloAction(array $names)
        {
            return "Hello ".implode(', ', $names);
        }

        /**
         * @Soap\Method("welcome")
         * @Soap\Param("names", phpType = "string[]")
         * @Soap\Result(phpType = "string")
         */
        public function welcomeAction($names)
        {
            return "Welcome ".implode(', ', $names);
        }

        public function setContainer(ContainerInterface $container = null)
        {
            parent::setContainer($container);

            $this->checkApiKeyHeader();
        }

        private function checkApiKeyHeader()
        {
            $soapHeaders = $this->container->get('request')->getSoapHeaders();

            // You can use '1234' !== (string) $soapHeaders->get('api_key')
            if (!$soapHeaders->has('api_key') || '1234' !== $soapHeaders->get('api_key')->getData()) {
                throw new \SoapFault("INVALID_API_KEY", "The api_key is invalid.");
            }
        }
    }
