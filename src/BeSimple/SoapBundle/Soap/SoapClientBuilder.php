<?php

namespace BeSimple\SoapBundle\Soap;

use BeSimple\SoapCommon\Classmap;
use BeSimple\SoapCommon\Converter\TypeConverterCollection;
use BeSimple\SoapClient\SoapClientBuilder as BaseSoapClientBuilder;

class SoapClientBuilder extends BaseSoapClientBuilder
{
    protected $soapClient;

    public function __construct($wsdl, array $options, Classmap $classmap = null, TypeConverterCollection $converters = null)
    {
        parent::__construct();

        $this->checkOptions($options);

        $this
            ->withWsdl($wsdl)
            ->withTrace($options['debug'])
        ;

        if (isset($options['user_agent'])) {
            $this->withUserAgent($options['user_agent']);
        }

        if (isset($options['cache_type'])) {
            $this->withWsdlCache($options['cache_type']);
        }

        if ($classmap) {
            $this->withClassmap($classmap);
        }

        if ($converters) {
            $this->withTypeConverters($converters);
        }
    }

    public function build()
    {
        if (!$this->soapClient) {
            $this->soapClient = parent::build();
        }

        return $this->soapClient;
    }

    protected function checkOptions(array $options)
    {
        $checkOptions = array(
            'debug'      => false,
            'cache_type' => null,
            'exceptions' => true,
            'user_agent' => 'BeSimpleSoap',
        );

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid   = array();
        $isInvalid = false;
        foreach ($options as $key => $value) {
            if (!array_key_exists($key, $checkOptions)) {
                $isInvalid = true;
                $invalid[] = $key;
            }
        }

        if ($isInvalid) {
            throw new \InvalidArgumentException(sprintf(
                'The "%s" class does not support the following options: "%s".',
                get_class($this),
                implode('\', \'', $invalid)
            ));
        }
    }
}
