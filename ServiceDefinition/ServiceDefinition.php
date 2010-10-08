<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition;

use Bundle\WebServiceBundle\Util\Collection;

class ServiceDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \Bundle\WebServiceBundle\Util\Collection
     */
    private $methods;

    /**
     * @var \Bundle\WebServiceBundle\Util\Collection
     */
    private $headers;

    public function __construct($name = null, array $methods = array(), array $headers = array())
    {
        $this->setName($name);
        $this->setMethods($methods);
        $this->setHeaders($headers);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return \Bundle\WebServiceBundle\Util\Collection
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     */
    public function setMethods($methods)
    {
        $this->methods = new Collection('getName');
        $this->methods->addAll($methods);
    }

    /**
     * @return \Bundle\WebServiceBundle\Util\Collection
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = new Collection('getName');
        $this->headers->addAll($headers);
    }
}