<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\Converter;

/**
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
use Bundle\WebServiceBundle\SoapKernel;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ConverterRepository
{
    private $typeConverters = array();

    public function __construct()
    {
    }

    public function addTypeConverter(TypeConverterInterface $converter)
    {
        $this->typeConverters[] = $converter;
    }

    public function getTypeConverters()
    {
        return $this->typeConverters;
    }

    public function registerTypeConverterServices(ContainerInterface $container)
    {
    }
}