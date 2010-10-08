<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Loader;

use Bundle\WebServiceBundle\ServiceDefinition\ServiceDefinition;
use Bundle\WebServiceBundle\ServiceDefinition\Header;
use Bundle\WebServiceBundle\ServiceDefinition\Method;
use Bundle\WebServiceBundle\ServiceDefinition\Argument;
use Bundle\WebServiceBundle\ServiceDefinition\Type;

class XmlFileLoader extends FileLoader
{
    public function loadServiceDefinition(ServiceDefinition $definition)
    {
        $xml = $this->parseFile($this->file);

        if($definition->getName() != $xml['name'])
        {
            throw new \InvalidArgumentException();
        }

        foreach($xml->header as $header)
        {
            $definition->getHeaders()->add($this->parseHeader($header));
        }

        foreach($xml->method as $method)
        {
            $definition->getMethods()->add($this->parseMethod($method));
        }
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \Bundle\WebServiceBundle\ServiceDefinition\Header
     */
    protected function parseHeader(\SimpleXMLElement $node)
    {
        $header = new Header((string)$node['name'], $this->parseType($node->type));

        return $header;
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \Bundle\WebServiceBundle\ServiceDefinition\Method
     */
    protected function parseMethod(\SimpleXMLElement $node)
    {
        $method = new Method((string)$node['name'], (string)$node['controller']);

        foreach($node->argument as $argument)
        {
            $method->getArguments()->add($this->parseArgument($argument));
        }

        return $method;
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \Bundle\WebServiceBundle\ServiceDefinition\Argument
     */
    protected function parseArgument(\SimpleXMLElement $node)
    {
        $argument = new Argument((string)$node['name'], $this->parseType($node->type));

        return $argument;
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \Bundle\WebServiceBundle\ServiceDefinition\Type
     */
    protected function parseType(\SimpleXMLElement $node)
    {
        $namespaces = $node->getDocNamespaces(true);
        $qname = explode(':', $node['xml-type'], 2);
        $xmlType = sprintf('{%s}%s', $namespaces[$qname[0]], $qname[1]);

        $type = new Type((string)$node['php-type'], $xmlType, (string)$node['converter']);

        return $type;
    }

    /**
     * @param  string $file
     *
     * @return \SimpleXMLElement
     */
    protected function parseFile($file)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->load($file, LIBXML_COMPACT)) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors()));
        }
        if (!$dom->schemaValidate(__DIR__.'/schema/servicedefinition-1.0.xsd')) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors()));
        }
        $dom->validateOnParse = true;
        $dom->normalizeDocument();
        libxml_use_internal_errors(false);

        return simplexml_import_dom($dom);
    }

    protected function getXmlErrors()
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return $errors;
    }
}