Configuration
=============

Client configuration
--------------------

Configure your first client in your config file:

.. code-block:: yaml

    # app/config/config.yml
    be_simple_soap:
        clients:
            DemoApi:
                # required
                wsdl: http://localhost/app_dev.php/ws/DemoApi?wsdl

                # classmap (optional)
                classmap:
                    type_name: "Full\Class\Name"

                # proxy (optional)
                proxy:
                    host:     proxy.domain.name # required to enable proxy configuration
                    port:     3128
                    login:    ~
                    password: ~
                    auth:     ~ # can be 'basic' or 'ntlm'

Using client
------------

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class DemoController extends Controller
    {
        public function helloAction($name)
        {
            // The client service name is `besimple.soap.client.demoapi`:
            // `besimple.soap.client.`: is the base name of your client
            // `demoapi`: is the name specified in your config file converted to lowercase
            $client = $this->container->get('besimple.soap.client.demoapi');

            // call `hello` method on WebService with the string parameter `$name`
            $helloResult = $client->hello($name);

            return $this->render('AcmeDemoBundle:Demo:hello.html.twig', array(
                'hello' => $helloResult,
            ));
        }
    }

Classmap
--------

Configuration
~~~~~~~~~~~~~

.. code-block:: yaml

    # app/config/config.yml
    be_simple_soap:
        clients:
            DemoApi:
                # ...
                classmap:
                    User: Acme\DemoBundle\Api\UserApi
                    # add other type_name: classname

UserApi class
~~~~~~~~~~~~~

.. code-block:: php

    namespace Acme\DemoBundle\Api;

    class UserApi
    {
        private $username;

        private $firstname;

        private $lastname;

        public function __construct($username)
        {
            $this->username = $username;
        }

        public function getFirstname()
        {
            return $this->firstname;
        }

        public function getLastname()
        {
            return $this->lastname;
        }
    }

Usage
~~~~~

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    use Acme\DemoBundle\Api\UserApi;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class DemoController extends Controller
    {
        public function userAction($username)
        {
            // The client service name is `besimple.soap.client.demoapi`:
            // `besimple.soap.client.`: is the base name of your client
            // `demoapi`: is the name specified in your config file converted to lowercase
            $client = $this->container->get('besimple.soap.client.demoapi');

            // call `getUser` method on WebService with an instance of UserApi
            // if the `getUserByUsername` method return a `User` type then `$userResult` is an instance of UserApi
            $userResult = $client->getUserByUsername($username);

            return $this->render('AcmeDemoBundle:Demo:user.html.twig', array(
                'user' => $userResult,
            ));
        }
    }

Without classmap configuration the `$userResult` is an instance of `stdClass`:

.. code-block:: text

    object(stdClass)#5561 (3) {
      ["username"]=>
      string(6) "FooBar"
      ["firstname"]=>
      string(3) "Foo"
      ["lastname"]=>
      string(3) "Bar"
    }

With classmap configuration the `$userResult` is an instance of `Acme\DemoBundle\Api\UserApi`:

.. code-block:: text

    object(Acme\DemoBundle\Api\UserApi)#208 (3) {
      ["username":"Acme\DemoBundle\Api\UserApi":private]=>
      string(6) "FooBar"
      ["firstname":"Acme\DemoBundle\Api\UserApi":private]=>
      string(3) "Foo"
      ["lastname":"Acme\DemoBundle\Api\UserApi":private]=>
      string(3) "Bar"
    }
