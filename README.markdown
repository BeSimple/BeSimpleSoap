WebServiceBundle
================

The WebServiceBundle is a Symfony2 bundle to build WSDL and SOAP based web services.
It is based on the [ckWebServicePlugin] [1] for symfony.

Requirements
------------

 * Install and enable PHP's `SOAP` extension
 * Download `Zend\Soap`

        git submodule add http://github.com/zendframework/zf2.git vendor/zend-framework

 * Add `Zend\Soap` library to `app/autoload.php`
 
        // app/autoload.php
        $loader->registerNamespaces(array(
            'Zend\\Soap' => __DIR__.'/../vendor/zend-frameword/library',
            // your other namespaces
        ));

QuickStart
----------

 *  Put WebServiceBundle in your `vendor/bundles/Bundle` dir

        git submodule add https://github.com/BeSimple/BeSimpleSoapBundle.git vendor/bundles/WebServiceBundle

 *  Enable WebServiceBundle in your `app/AppKernel.php`

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new new Bundle\WebServiceBundle\WebServiceBundle(),
                // ...
            );
        }

 *  Register the Bundle namespace

        // app/autoload.php
        $loader->registerNamespaces(array(
            'Bundle'     => __DIR__.'/../vendor/bundles',
            'Zend\\Soap' => __DIR__.'/../vendor/zend-frameword/library',
            // your other namespaces
        ));

 *  Include the WebServiceBundle's routing configuration in `app/config/routing.yml` (you can choose the prefix arbitrarily)

        _ws:
            resource: "@WebServiceBundle/Resources/config/routing/webservicecontroller.xml"
            prefix:   /ws

 *  Configure your first web service in `app/config/config.yml`

        web_service:
            services:
                DemoApi:
                    namespace:     http://mysymfonyapp.com/ws/DemoApi/1.0/
                    binding:       rpc-literal
                    resource:      "@AcmeDemoBundle/Controller/DemoController.php"
                    resource_type: annotation

 *  Annotate your controller methods

        // src/Acme/DemoBundle/Controller/DemoController.php
        use Bundle\WebServiceBundle\ServiceDefinition\Annotation\Method;
        use Bundle\WebServiceBundle\ServiceDefinition\Annotation\Param;
        use Bundle\WebServiceBundle\ServiceDefinition\Annotation\Result;
        use Bundle\WebServiceBundle\Soap\SoapResponse;

        class DemoController extends Controller
        {
            /**
             * @Method("Hello")
             * @Param("name", phpType = "string")
             * @Result(phpType = "string")
             */
            public function helloAction($name)
            {
                return new SoapResponse(sprintf('Hello %s!', $name));
            }
        }

 *  Open your web service endpoint

     *   `http://localhost/app_dev.php/ws/DemoApi` - HTML documentation
     *   `http://localhost/app_dev.php/ws/DemoApi?wsdl` - WSDL file

Test
----

    phpunit -c phpunit.xml.dist

[1]: http://www.symfony-project.org/plugins/ckWebServicePlugin