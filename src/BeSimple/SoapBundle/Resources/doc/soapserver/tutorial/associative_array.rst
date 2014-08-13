Associative Array
=================

Pre-existent Type
-----------------

+------------------------------------------------+-----------------+
|                  Php Type                      |   Value Type    |
+================================================+=================+
| BeSimple\\SoapCommon\\Type\\KeyValue\\String   | String          |
+------------------------------------------------+-----------------+
| BeSimple\\SoapCommon\\Type\\KeyValue\\Boolean  | Boolean         |
+------------------------------------------------+-----------------+
| BeSimple\\SoapCommon\\Type\\KeyValue\\Int      | Int             |
+------------------------------------------------+-----------------+
| BeSimple\\SoapCommon\\Type\\KeyValue\\Float    | Float           |
+------------------------------------------------+-----------------+
| BeSimple\\SoapCommon\\Type\\KeyValue\\Date     | DateTime object |
+------------------------------------------------+-----------------+
| BeSimple\\SoapCommon\\Type\\KeyValue\\DateTime | DateTime object |
+------------------------------------------------+-----------------+

Controller
----------

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use Symfony\Component\DependencyInjection\ContainerAware;

    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("returnAssocArray")
         * @Soap\Result(phpType = "BeSimple\SoapCommon\Type\KeyValue\String[]")
         */
        public function assocArrayOfStringAction()
        {
            return array(
                'foo' => 'bar',
                'bar' => 'foo',
            );
        }

        /**
         * @Soap\Method("sendAssocArray")
         * @Soap\Param("assocArray", phpType = "BeSimple\SoapCommon\Type\KeyValue\String[]")
         * @Soap\Result(phpType = "BeSimple\SoapCommon\Type\KeyValue\String[]")
         */
        public function sendAssocArrayOfStringAction(array $assocArray)
        {
            // The $assocArray it's a real associative array
            // var_dump($assocArray);die;

            return $assocArray;
        }
    }

How to create my Associative Array?
-----------------------------------

.. code-block:: php

    namespace Acme\DemoBundle\Soap\Type;

    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use BeSimple\SoapCommon\Type\AbstractKeyValue;

    class User extends AbstractKeyValue
    {
        /**
         * @Soap\ComplexType("Acme\DemoBundle\Entity\User")
         */
        protected $value;
    }

.. code-block:: php

    namespace Acme\DemoBundle\Controller;

    use Acme\DemoBundle\Entity\User;
    use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
    use Symfony\Component\DependencyInjection\ContainerAware;

    class DemoController extends ContainerAware
    {
        /**
         * @Soap\Method("getUsers")
         * @Soap\Result(phpType = "Acme\DemoBundle\Soap\Type\User[]")
         */
        public function getUsers()
        {
            return array(
                'user1' => new User('user1', 'user1@user.com'),
                'user2' => new User('user2', 'user2@user.com'),
            );
        }
    }
