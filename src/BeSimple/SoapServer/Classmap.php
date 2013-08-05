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

namespace BeSimple\SoapServer;

use BeSimple\SoapCommon\Classmap as BaseClassmap;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Classmap extends BaseClassmap
{
    protected $classmapInversed = array();

    /**
     * {@inheritdoc}
     */
    public function add($type, $classname)
    {
        parent::add($type, $classname);

        $this->classmapInversed[$classname] = $type;
    }

    public function getByClassname($classname)
    {
        if (!$this->hasByClassname($classname)) {
            throw new \InvalidArgumentException(sprintf('The classname "%s" was not found in %s', $classname, __CLASS__));
        }

        return $this->classmapInversed[$classname];
    }

    public function hasByClassname($classname)
    {
        return isset($this->classmapInversed[$classname]);
    }
}
