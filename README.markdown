WebServiceBundle
================

The WebServiceBundle is a Symfony2 bundle to build WSDL and SOAP based web services.
It is based on the [ckWebServicePlugin] [1] for symfony.

Installation
------------

Put WebServiceBundle in your `src/Bundle` dir. Enable PHP's `SOAP` extension.

Create a new front controller script (like `index.php`) for your web service endpoint, e.g. `webservice.php`.
Change the environment passed to the kernel constructor, e.g. to `soap`, in this new front controller script. 

Configure the WebServiceBundle in the configuration file for this new environment (e.g. `config_soap.yml`):

    webservice.config:
        definition:
            name: MyWebService
        binding:
            style: rpc-literal

Test
----

    phpunit -c myapp src/Bundle/WebServiceBundle

[1]: http://www.symfony-project.org/plugins/ckWebServicePlugin