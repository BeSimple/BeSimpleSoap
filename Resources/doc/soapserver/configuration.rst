Configuration
=============

Routing
-------

Include the `BeSimpleSoapBundle`'s routing configuration in your routing file (you can choose the prefix arbitrarily):

.. code-block:: yaml

    # app/config/routing.yml
    _besimple_soap:
        resource: "@BeSimpleSoapBundle/Resources/config/routing/webservicecontroller.xml"
        prefix:   /ws

Config
------

Configure your first web service in your config file:

.. code-block:: yaml

    # app/config/config.yml
    be_simple_soap:
        services:
            DemoApi:
                namespace:     http://mysymfonyapp.com/ws/DemoApi/1.0/
                binding:       rpc-literal
                resource:      "@AcmeDemoBundle/Controller/DemoController.php"
                resource_type: annotation

Annotations for Controllers
---------------------------

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use Symfony\Component\DependencyInjection\ContainerAware;

    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("hello")
         * @Soap\Param("name", phpType = "string")
         * @Soap\Result(phpType = "string")
         */
        public function helloAction($name)
        {
            return sprintf('Hello %s!', $name);
        }

        /**
         * @Soap\Method("goodbye")
         * @Soap\Param("name", phpType = "string")
         * @Soap\Result(phpType = "string")
         */
        public function goodbyeAction($name)
        {
            return $this->container->get('besimple.soap.response')->setReturnValue(sprintf('Goodbye %s!', $name));
        }
    }

Get your WSDL
-------------

To access your WSDL go to the following address: http://localhost/app_dev.php/ws/DemoApi?wsdl

To read the WSDL in your browser you can call this address (without `wsdl` parameter): http://localhost/app_dev.php/ws/DemoApi
