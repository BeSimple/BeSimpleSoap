<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapClient;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class SoapRequest
{
    protected $function;
    protected $arguments;
    protected $options;

    public function __construct($function = null, array $arguments = array(), array $options = array())
    {
        $this->function  = $function;
        $this->arguments = $arguments;
        $this->options   = $options;
    }

    /**
     * @return string The function name
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param string The function name
     *
     * @return SoapRequest
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * @return array An array with all arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param string The name of the argument
     * @param mixed  The default value returned if the argument is not exists
     *
     * @return mixed
     */
    public function getArgument($name, $default = null)
    {
        return $this->hasArgument($name) ? $this->arguments[$name] : $default;
    }

    /**
     * @param string The name of the argument
     *
     * @return boolean
     */
    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    /**
     * @param array An array with arguments
     *
     * @return SoapRequest
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @param string The name of argument
     * @param mixed  The value of argument
     *
     * @return SoapRequest
     */
    public function addArgument($name, $value)
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    /**
     * @return array An array with all options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string The name of the option
     * @param mixed  The default value returned if the option is not exists
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return $this->hasOption($name) ? $this->options[$name] : $default;
    }

    /**
     * @param string The name of the option
     *
     * @return boolean
     */
    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * @param array An array with options
     *
     * @return SoapRequest
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string The name of option
     * @param mixed  The value of option
     *
     * @return SoapRequest
     */
    public function addOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }
    
}