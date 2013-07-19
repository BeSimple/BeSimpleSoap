Installation with Composer
==========================

Add `besimple/soap-bundle <https://packagist.org/packages/besimple/soap-bundle>`_ (with vendors) in your composer.json:

.. code-block:: json

    {
        "require": {
            "besimple/soap-bundle": "dev-master",
            "besimple/soap-common": "dev-master",
            "ass/xmlsecurity":      "dev-master"
        }
    }

To install the server please add `besimple/soap-server <https://packagist.org/packages/besimple/soap-server>`_ in your composer.json:

.. code-block:: json

    {
        "require": {
            "besimple/soap-server": "dev-master"
        }
    }

To install the client please add `besimple/soap-client <https://packagist.org/packages/besimple/soap-client>`_ in your composer.json:

.. code-block:: json

    {
        "require": {
            "besimple/soap-client": "dev-master"
        }
    }

Run this command to download the new vendors:

.. code-block:: bash

    $ php composer.phar self-update
    $ php composer.phar update

Enable the `BeSimpleSoapBundle <https://github.com/BeSimple/BeSimpleSoapBundle>`_ in your Kernel class

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
