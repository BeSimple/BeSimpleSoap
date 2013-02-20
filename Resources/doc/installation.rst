Installation
============

Requirements
------------

Install and enable PHP's SOAP extension.

With Composer
~~~~~~~~~~~~~

Add `besimple/soap-bundle` (with vendors) in your composer.json:

.. code-block:: json

    {
        "require": {
            "besimple/soap-bundle": "dev-master",
            "besimple/soap-common": "dev-master",
            "ass/xmlsecurity":      "dev-master"
        }
    }

To install the server please add `besimple/soap-server` in your composer.json:

.. code-block:: json

    {
        "require": {
            "besimple/soap-server": "dev-master"
        }
    }

To install the client please add `besimple/soap-client` in your composer.json:

.. code-block:: json

    {
        "require": {
            "besimple/soap-client": "dev-master"
        }
    }

.. code-block:: bash

    $ php composer.phar update

Enable the `BeSimpleSoapBundle` in your Kernel class

.. code-block:: php

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new BeSimple\SoapBundle\BeSimpleSoapBundle(),
            // ...
        );
    }

With deps file (outdated)
~~~~~~~~~~~~~~~~~~~~~~~~~

Download `BeSimple\\SoapCommon`_ and `BeSimple\\SoapServer`_ (only for the server part) and/or `BeSimple\\SoapClient`_ (only for ther client part).

.. code-block:: ini

    ; deps file
    [BeSimple\SoapCommon]
        git=https://github.com/BeSimple/BeSimpleSoapCommon.git
        target=besimple-soapcommon

    [BeSimple\SoapClient]
        git=https://github.com/BeSimple/BeSimpleSoapClient.git
        target=besimple-soapclient

    [BeSimple\SoapServer]
        git=https://github.com/BeSimple/BeSimpleSoapServer.git
        target=besimple-soapserver


Add `BeSimple` libraries in autoload.php

.. code-block:: php

    // app/autoload.php
    $loader->registerNamespaces(array(
        'BeSimple\\SoapCommon' => __DIR__.'/../vendor/besimple-soapcommon/src',
        'BeSimple\\SoapServer' => __DIR__.'/../vendor/besimple-soapserver/src',
        'BeSimple\\SoapClient' => __DIR__.'/../vendor/besimple-soapclient/src',
        // your other namespaces
    ));

Download `Zend\\Soap`_ and `Zend\\Mime`_ or add in `deps` file. `Zend` library is required only for the server part.

.. code-block:: ini

    ; deps file
    [Zend\Soap]
        git=http://github.com/BeSimple/zend-soap.git
        target=/zend-framework/library/Zend/Soap

    [Zend\Mime]
        git=http://github.com/BeSimple/zend-mime.git
        target=/zend-framework/library/Zend/Mime

Add `Zend` library in autoload.php

.. code-block:: php

    // app/autoload.php
    $loader->registerNamespaces(array(
        'Zend' => __DIR__.'/../vendor/zend-framework/library',
        // your other namespaces
    ));

Installation
````````````

`Download`_ the bundle or add in `deps` file

.. code-block:: ini

    ; deps file
    [BeSimpleSoapBundle]
        git=http://github.com/BeSimple/BeSimpleSoapBundle.git
        target=/bundles/BeSimple/SoapBundle

Add `BeSimple` in autoload.php

.. code-block:: php

    // app/autoload.php
    $loader->registerNamespaces(array(
        'BeSimple' => __DIR__.'/../vendor/bundles',
        // your other namespaces
    ));

Add `BeSimpleSoapBundle` in your Kernel class

.. code-block:: php

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new BeSimple\SoapBundle\BeSimpleSoapBundle(),
            // ...
        );
    }

.. _`Zend\\Soap`: http://github.com/BeSimple/zend-soap
.. _`Zend\\Mime`: http://github.com/BeSimple/zend-mime
.. _`BeSimple\\SoapCommon`: http://github.com/BeSimple/BeSimpleSoapCommon
.. _`BeSimple\\SoapServer`: http://github.com/BeSimple/BeSimpleSoapServer
.. _`BeSimple\\SoapClient`: http://github.com/BeSimple/BeSimpleSoapClient
.. _`Download`: http://github.com/BeSimple/BeSimpleSoapBundle
