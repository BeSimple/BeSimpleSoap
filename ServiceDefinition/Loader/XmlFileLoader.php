<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Loader;

use BeSimple\SoapBundle\ServiceDefinition\Argument;
use BeSimple\SoapBundle\ServiceDefinition\Header;
use BeSimple\SoapBundle\ServiceDefinition\Method;
use BeSimple\SoapBundle\ServiceDefinition\Type;
use BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition;

use Symfony\Component\Config\Loader\FileLoader;

class XmlFileLoader extends FileLoader
{
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $xml  = $this->parseFile($path);

        $definition = new ServiceDefinition();
        $definition->setName((string) $xml['name']);
        $definition->setNamespace((string) $xml['namespace']);

        foreach($xml->header as $header) {
            $definition->getHeaders()->add($this->parseHeader($header));
        }

        foreach($xml->method as $method) {
            $definition->getMethods()->add($this->parseMethod($method));
        }

        return $definition;
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\Header
     */
    protected function parseHeader(\SimpleXMLElement $node)
    {
        return new Header((string)$node['name'], $this->parseType($node->type));
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\Method
     */
    protected function parseMethod(\SimpleXMLElement $node)
    {
        $method = new Method((string)$node['name'], (string)$node['controller']);

        foreach($node->argument as $argument) {
            $method->getArguments()->add($this->parseArgument($argument));
        }

        $method->setReturn($this->parseType($node->return->type));

        return $method;
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\Argument
     */
    protected function parseArgument(\SimpleXMLElement $node)
    {
        $argument = new Argument((string)$node['name'], $this->parseType($node->type));

        return $argument;
    }

    /**
     * @param \SimpleXMLElement $node
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\Type
     */
    protected function parseType(\SimpleXMLElement $node)
    {
        $namespaces = $node->getDocNamespaces(true);
        $qname      = explode(':', $node['xml-type'], 2);
        $xmlType    = sprintf('{%s}%s', $namespaces[$qname[0]], $qname[1]);

        return new Type((string)$node['php-type'], $xmlType, (string)$node['converter']);
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