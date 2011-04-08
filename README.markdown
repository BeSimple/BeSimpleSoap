WebServiceBundle
================

The WebServiceBundle is a Symfony2 bundle to build WSDL and SOAP based web services.
It is based on the [ckWebServicePlugin] [1] for symfony.

Requirements
------------

 * Install and enable PHP's `SOAP` extension
 * Download and add `Zend\Soap` library to `app/autoload.php`

QuickStart
----------

 *  Put WebServiceBundle in your `src/Bundle` dir
 *  Enable WebServiceBundle in your `app/AppKernel.php`
 
 *  Include the WebServiceBundle's routing configuration in `app/config/routing.yml` (you can choose the prefix arbitrarily)
   
        _ws:
            resource: "@WebServiceBundle/Resources/config/routing/webservicecontroller.xml"
            prefix:   /ws
          
 *  Configure your first web service in `app/config/config.yml`
        
        web_service:
            services:
                demoapi:
                    name:          DemoApi
                    namespace:     http://mysymfonyapp.com/ws/DemoApi/1.0/                  
                    binding:       rpc-literal
                    resource:      "@AcmeDemoBundle/Controller/DemoController.php"
                    resource_type: annotation

 *  Annotate your controller methods
 
        // src/Acme/DemoBundle/Controller/DemoController.php
        /**
         * @ws:Method("Hello")
         * @ws:Param("name", type = "string")
         * @ws:Result(type = "string")
         */
        public function helloAction($name)
        {
            return new SoapResponse(sprintf('Hello %s!', $name));
        }

 *  Open your web service endpoint

     *   `http://localhost/app_dev.php/ws/DemoApi` - HTML documentation
     *   `http://localhost/app_dev.php/ws/DemoApi?WSDL` - WSDL file

Test
----

    phpunit -c myapp src/Bundle/WebServiceBundle

[1]: http://www.symfony-project.org/plugins/ckWebServicePlugin