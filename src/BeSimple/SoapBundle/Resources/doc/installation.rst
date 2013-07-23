Installation with Composer
==========================

Add `besimple/soap-bundle <https://packagist.org/packages/besimple/soap-bundle>`_ (with vendors) in your composer.json:

.. code-block:: json

    {
        "require": {
            "besimple/soap":   "0.1.*@dev,
            "ass/xmlsecurity": "dev-master"
        }
    }

Update vendors:

.. code-block:: bash

    $ php composer.phar self-update
    $ php composer.phar update

Enable the BeSimpleSoapBundle your application Kernel class:

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
