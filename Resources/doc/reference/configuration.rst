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

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use BeSimple\SoapBundle\Soap\SoapResponse;

    class DemoController extends Controller
    {
        /**
         * @Soap\Method("Hello")
         * @Soap\Param("name", phpType = "string")
         * @Soap\Result(phpType = "string")
         */
        public function helloAction($name)
        {
            return new SoapResponse(sprintf('Hello %s!', $name));
        }
    }