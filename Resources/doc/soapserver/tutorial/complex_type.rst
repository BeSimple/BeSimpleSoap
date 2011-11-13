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
    use Symfony\Component\DependencyInjection\ContainerAware;

    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("getUser")
         * @Soap\Param("name", phpType = "string")
         * @Soap\Result(phpType = "My\App\Entity\User")
         */
        public function getUserAction($name)
        {
            $user = $this->container->getDoctrine()->getRepository('MyApp:User')->findOneBy(array(
                'name' => $name,
            ));

            if (!$user) {
                throw new \SoapFault('USER_NOT_FOUND', sprintf('The user with the name "%s" can not be found', $name));
            }

            return $this->container->get('besimple.soap.response')->setReturnValue($user);
        }
    }

User class
----------

You can expose only the properties (public, protected or private) of a complex type.

.. code-block:: php

    namespace My\App\Entity;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

    class User
    {
        /**
         * @Soap\ComplexType("string")
         */
        public $firstname;

        /**
         * @Soap\ComplexType("string")
         */
        public $lastname;

        /**
         * @Soap\ComplexType("int", nillable=true)
         */
        private $id;

        /**
         * @Soap\ComplexType("string")
         */
        private $username;

        /**
         * @Soap\ComplexType("string")
         */
        private $email;
    }

ComplexType
-----------

`ComplexType` accepts the following options:

    * nillable: To specify that the value can be null