BeSimpleSoapBundle
==================

The BeSimpleSoapBundle is a Symfony2 bundle to build WSDL and SOAP based web services.
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

 *  Put BeSimplSoapBundle in your `vendor/bundles/BeSimple` dir

        git submodule add https://github.com/BeSimple/BeSimpleSoapBundle.git vendor/bundles/BeSimple/SoapBundle

 *  Enable BeSimpleSoapBundle in your `app/AppKernel.php`

        // app/AppKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new new BeSimple\SoapBundle\BeSimpleSoapBundle(),
                // ...
            );
        }

 *  Register the BeSimple namespace

        // app/autoload.php
        $loader->registerNamespaces(array(
            'BeSimple'   => __DIR__.'/../vendor/bundles',
            'Zend\\Soap' => __DIR__.'/../vendor/zend-frameword/library',
            // your other namespaces
        ));

 *  Include the BeSimpleSoapBundle's routing configuration in `app/config/routing.yml` (you can choose the prefix arbitrarily)

        _besimple_soap:
            resource: "@BeSimpleSoapBundle/Resources/config/routing/webservicecontroller.xml"
            prefix:   /ws

 *  Configure your first web service in `app/config/config.yml`

        be_simple_soap:
            services:
                DemoApi:
                    namespace:     http://mysymfonyapp.com/ws/DemoApi/1.0/
                    binding:       rpc-literal
                    resource:      "@AcmeDemoBundle/Controller/DemoController.php"
                    resource_type: annotation

 *  Annotate your controller methods

        // src/Acme/DemoBundle/Controller/DemoController.php
        use BeSimple\SoapBundle\ServiceDefinition\Annotation\Method;
        use BeSimple\SoapBundle\ServiceDefinition\Annotation\Param;
        use BeSimple\SoapBundle\ServiceDefinition\Annotation\Result;
        use BeSimple\SoapBundle\Soap\SoapResponse;

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
