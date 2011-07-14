<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Util;

class Collection implements \IteratorAggregate, \Countable
{
    private $elements = array();
    private $keyPropertyGetter;

    public function __construct($keyPropertyGetter)
    {
        $this->keyPropertyGetter = $keyPropertyGetter;
    }

    public function add($element)
    {
        $this->elements[call_user_func(array($element, $this->keyPropertyGetter))] = $element;
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

    public function getIterator ()
    {
        return new \ArrayIterator($this->elements);
    }
}