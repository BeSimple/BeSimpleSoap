Cache
=====

Configuration
-------------

Configure the cache of PHP Soap WSDL in your config file:

.. code-block:: yaml

    # app/config/config.yml
    be_simple_soap:
        cache:
            type:     disk
            lifetime: 86400
            limit:    5

The cache type can be: **none**, **disk** (default value), **memory**, **disk_memory**.

The lifetime in seconds of a WSDL file in the cache (**86400 is the default value by PHP**).

The limit is the maximum number of in-memory cached WSDL files (**5 is the default value by PHP**).

The WSDL files cached are written in cache folder of your Symfony2 application.

If you want more information you can visit the following page `PHP Soap runtime configuration <http://www.php.net/manual/en/soap.configuration.php>`_.
