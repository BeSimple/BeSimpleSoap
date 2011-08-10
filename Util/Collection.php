<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Util;

class Collection implements \IteratorAggregate, \Countable
{
    private $elements = array();
    private $getter;
    private $class;

    public function __construct($getter, $class = null)
    {
        $this->getter = $getter;
        $this->class  = $class;
    }

    public function add($element)
    {
        if ($this->class && !$element instanceof $this->class) {
            throw new \InvalidArgumentException(sprintf('Cannot add class "%s" because it is not an instance of "%s"', get_class($element), $this->class));
        }

        $this->elements[$element->{$this->getter}()] = $element;
    }

    public function addAll($elements)
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    public function has($key)
    {
        return isset($this->elements[$key]);
    }

    public function get($key)
    {
        return $this->has($key) ? $this->elements[$key] : null;
    }

    public function clear()
    {
        $this->elements = array();
    }

    public function count()
    {
        return count($this->elements);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }
}