<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\Converter;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class ConverterRepository
{
    private $typeConverters = array();

    public function addTypeConverter(TypeConverterInterface $converter)
    {
        $this->typeConverters[] = $converter;
    }

    public function getTypeConverters()
    {
        return $this->typeConverters;
    }
}
