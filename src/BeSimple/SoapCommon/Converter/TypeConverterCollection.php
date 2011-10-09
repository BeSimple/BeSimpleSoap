<?php

/*
 * This file is part of the BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Converter;

/**
 * @author Christian Kerl <christian-kerl@web.de>
 */
class TypeConverterCollection
{
    private $converters = array();

    public function all()
    {
        return array_values($this->converters);
    }

    public function get($namespace, $name)
    {
        if (!$this->has($namespace, $name)) {
            throw new \InvalidArgumentException(sprintf('The converter "%s %s" does not exists', $namespace, $name));
        }

        return $this->converters[$namespace.':'.$name];
    }

    public function add(TypeConverterInterface $converter)
    {
        if ($this->has($converter->getTypeNamespace(), $converter->getTypeName())) {
            throw new \InvalidArgumentException(sprintf('The converter "%s %s" already exists', $converter->getTypeNamespace(), $converter->getTypeName()));
        }

        $this->converters[$converter->getTypeNamespace().':'.$converter->getTypeName()] = $converter;
    }

    public function set(array $converters)
    {
        $this->converters = array();

        foreach ($converters as $converter) {
            $this->add($converter);
        }
    }

    public function has($namespace, $name)
    {
        return isset($this->converters[$namespace.':'.$name]);
    }

    public function addCollection(TypeConverterCollection $converterCollection)
    {
        foreach ($converterCollection->all() as $converter) {
            $this->add($converter);
        }
    }

    /**
     * @return array
     */
    public function getTypemap()
    {
        $typemap = array();

        foreach ($this->converters as $converter) {
            $typemap[] = array(
                'type_name' => $converter->getTypeName(),
                'type_ns'   => $converter->getTypeNamespace(),
                'from_xml'  => function($input) use ($converter) {
                    return $converter->convertXmlToPhp($input);
                },
                'to_xml'    => function($input) use ($converter) {
                    return $converter->convertPhpToXml($input);
                },
            );
        }

        return $typemap;
    }
}
