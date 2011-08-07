Complex Type
============

This tutorial explains how to do to return a complex type.

If your SOAP function takes a complex type as input, this tutorial is
valid. You'll just have to adapt the input parameters of your method.


Controller
----------

.. code-block:: php

    namespace My\App\Controller;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use BeSimple\SoapBundle\Soap\SoapResponse;
    use Symfony\Component\DependencyInjection\ContainerAware;

    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("getUser")
         * @Soap\Param("name", phpType = "string")
         *
         * Specify \My\App\Entity\User phpType
         * Warning: Do not forget the first backslash
         * @Soap\Result(phpType = "\My\App\Entity\User")
         */
        public function getUserAction($name)
        {
            $user = $this->container->getDoctrine()->getRepository('MyApp:User')->findOneByName($name);

            if (!$user) {
                throw new \SoapFault('USER_NOT_FOUND', sprintf('The user with the name "%s" can not be found', $name));
            }

            return new SoapResponse($user);
        }
    }

User class
----------

You can expose public property and public method (getter and setter).

.. code-block:: php

    namespace My\App\Entity;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

    class User
    {
        /**
         * @Soap\PropertyComplexType("string")
         */
        public $firstname;

        /**
         * @Soap\PropertyComplexType("string")
         */
        public $lastname;

        private $id;
        private $username;
        private $email;

        /**
         * @Soap\MethodComplexType("int", name="user_id", nillable=true)
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @Soap\MethodComplexType("string", setter="setUsername")
         */
        public function getUsername()
        {
            return $this->username;
        }

        /**
         * @Soap\MethodComplexType("string", setter="setEmail")
         */
        public function getEmail()
        {
            return $this->email;
        }

        public function setUsername($username)
        {
            $this->username = $username;
        }

        public function setEmail($email)
        {
            $this->email = $email;
        }
    }

PropertyComplexType
-------------------

`PropertyComplexType` accepts the following options:

    * **name**: To override the original name of the property
    * **nillable**: To specify that the value can be null

MethodComplexType
-------------------

`MethodComplexType` accepts the following options:

    * **name**: To override the original name of the property
    * **nillable**: To specify that the value can be null
    * **setter**: The set method name value. *Mandatory if the complex type is passed as a parameter to a function.*