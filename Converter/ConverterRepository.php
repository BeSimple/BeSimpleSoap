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

    public function toSoapServerTypemap(SoapKernel $kernel)
    {
        $result = array();

        foreach($this->typeConverters as $typeConverter)
        {
            $result[] = array(
                'type_name' => $typeConverter->getTypeName(),
                'type_ns' => $typeConverter->getTypeNamespace(),
                'from_xml' => function($input) use ($kernel, $typeConverter) {
                    return $typeConverter->convertXmlToPhp($kernel->getRequest(), $input);
                },
                'to_xml' => function($input) use ($kernel, $typeConverter) {
                    return $typeConverter->convertPhpToXml($kernel->getResponse(), $input);
                }
            );
        }

        return $result;
    }

    public function registerTypeConverterServices(ContainerInterface $container)
    {
        foreach($container->findTaggedServiceIds('webservice.converter') as $id => $attributes)
        {
            $this->addTypeConverter($container->get($id));
        }
    }
}
