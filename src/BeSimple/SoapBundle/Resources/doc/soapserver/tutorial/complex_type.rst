Complex Type
============

This tutorial explains how to do to return a complex type.

If your SOAP function takes a complex type as input, this tutorial is
valid. You'll just have to adapt the input parameters of your method.


Controller
----------

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use Symfony\Component\DependencyInjection\ContainerAware;

    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("getUser")
         * @Soap\Param("name", phpType = "string")
         * @Soap\Result(phpType = "Acme\DemoBundle\Entity\User")
         */
        public function getUserAction($name)
        {
            $user = $this->container->getDoctrine()->getRepository('MyApp:User')->findOneBy(array(
                'name' => $name,
            ));

            if (!$user) {
                throw new \SoapFault('USER_NOT_FOUND', sprintf('The user with the name "%s" can not be found', $name));
            }

            return $user;
        }
    }

User class
----------

You can expose only the properties (public, protected or private) of a complex type.

**For performance reasons, we advise to create getter and setter for each property.**

.. code-block:: php

    namespace Acme\DemoBundle\Entity;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

    /**
     * @Soap\Alias("User")
     */
    class User
    {
        /**
         * @Soap\ComplexType("int", nillable=true)
         */
        private $id;

        /**
         * @Soap\ComplexType("string")
         */
        public $firstname;

        /**
         * @Soap\ComplexType("string")
         */
        public $lastname;

        /**
         * @Soap\ComplexType("string")
         */
        private $username;

        /**
         * @Soap\ComplexType("string")
         */
        private $email;

        /**
         * @Soap\ComplexType("boolean")
         */
        private $newsletter;

        /**
         * @Soap\ComplexType("date")
         */
        private $createdAt:

        /**
         * @Soap\ComplexType("datetime")
         */
        private $updatedAt;

        public function getId()
        {
            return $this->id;
        }

        public function getUsername()
        {
            return $this->username;
        }

        public function getEmail()
        {
            return $this->email;
        }

        public function getFirstname()
        {
            return $this->firstname;
        }

        public function setFirstname($firstname)
        {
            $this->firstname = $firstname;
        }

        public function getLastname()
        {
            return $this->lastname;
        }

        public function setLastname($lastname)
        {
            $this->lastname = $lastname;
        }

        public function hasNewsletter()
        {
            return $this->newsletter;
        }

        public function setNewsletter($newsletter)
        {
            $this->newletter = (Boolean) $newsletter;
        }

        public function getCreatedAt()
        {
            return $this->createdAt;
        }

        public function setCreatedAt(\DateTime $createdAt)
        {
            $this->createdAt = $createdAt;
        }

        public function getUpdatedAt()
        {
            return this->updatedAt;
        }

        public function setUpdatedAt(\DateTime $updatedAt)
        {
            $this->updatedAt = $updatedAt;
        }
    }

ComplexType
-----------

`ComplexType` accepts the following options:

    * nillable: To specify that the value can be null

Alias
-----

If you can Alias annotation, the name of your entity will be renamed in the WSDL generated.
With alias the name in WSDL will `User` instead of `Acme.DemoBundle.Entity.User` (name without Alias annotation).
