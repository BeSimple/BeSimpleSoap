<?php

/*
 * This file is part of the BeSimpleSoapClient.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @link https://github.com/BeSimple/BeSimpleSoapClient
 */

namespace BeSimple\SoapClient;

/**
 * Downloads WSDL files with cURL. Uses all SoapClient options for
 * authentication. Uses the WSDL_CACHE_* constants and the 'soap.wsdl_*'
 * ini settings. Does only file caching as SoapClient only supports a file
 * name parameter. The class also resolves remote XML schema includes.
 *
 * @author Andreas Schamberger
 */
class WsdlDownloader
{
    /**
     * Cache enabled.
     *
     * @var bool
     */
    private $cacheEnabled;

    /**
     * Cache dir.
     *
     * @var string
     */
    private $cacheDir;

    /**
     * Cache TTL.
     *
     * @var int
     */
    private $cacheTtl;

    /**
     * Options array
     *
     * @var array(string=>mixed)
     */
    private $options = array();

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        // get current WSDL caching config
        $this->cacheEnabled = (bool)ini_get('soap.wsdl_cache_enabled');
        if ($this->cacheEnabled === true
            && isset($options['cache_wsdl'])
            && $options['cache_wsdl'] === WSDL_CACHE_NONE) {
            $this->cacheEnabled = false;
        }
        $this->cacheDir = ini_get('soap.wsdl_cache_dir');
        if (!is_dir($this->cacheDir)) {
            $this->cacheDir = sys_get_temp_dir();
        }
        $this->cacheDir = rtrim($this->cacheDir, '/\\');
        $this->cacheTtl = ini_get('soap.wsdl_cache_ttl');
        $this->options = $options;
        if (!isset($this->options['resolve_xsd_includes'])) {
            $this->options['resolve_xsd_includes'] = true;
        }
    }

    /**
     * Download given WSDL file and return name of cache file.
     *
     * @param string $wsdl
     * @return string
     */
    public function download($wsdl)
    {
        // download and cache remote WSDL files or local ones where we want to
        // resolve remote XSD includes
        $isRemoteFile = $this->isRemoteFile($wsdl);
        if ($isRemoteFile === true || $this->options['resolve_xsd_includes'] === true) {
            $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'wsdl_' . md5($wsdl) . '.cache';
            if ($this->cacheEnabled === false
                || !file_exists($cacheFile)
                || (filemtime($cacheFile) + $this->cacheTtl) < time()) {
                if ($isRemoteFile === true) {
                    // new curl object for request
                    $curl = new Curl($this->options);
                    // execute request
                    $responseSuccessfull = $curl->exec($wsdl);
                    // get content
                    if ($responseSuccessfull === true) {
                        $response = $curl->getResponseBody();
                        if ($this->options['resolve_xsd_includes'] === true) {
                            $this->resolveXsdIncludes($response, $cacheFile, $wsdl);
                        } else {
                            file_put_contents($cacheFile, $response);
                        }
                    } else {
                        throw new \ErrorException("SOAP-ERROR: Parsing WSDL: Couldn't load from '" . $wsdl ."'");
                    }
                } elseif (file_exists($wsdl)) {
                    $response = file_get_contents($wsdl);
                    $this->resolveXsdIncludes($response, $cacheFile);
                } else {
                    throw new \ErrorException("SOAP-ERROR: Parsing WSDL: Couldn't load from '" . $wsdl ."'");
                }
            }
            return $cacheFile;
        } elseif (file_exists($wsdl)) {
            return realpath($wsdl);
        } else {
            throw new \ErrorException("SOAP-ERROR: Parsing WSDL: Couldn't load from '" . $wsdl ."'");
        }
    }

    /**
     * Do we have a remote file?
     *
     * @param string $file
     * @return boolean
     */
    private function isRemoteFile($file)
    {
        $isRemoteFile = false;
        // @parse_url to suppress E_WARNING for invalid urls
        if (($url = @parse_url($file)) !== false) {
            if (isset($url['scheme']) && substr($url['scheme'], 0, 4) == 'http') {
                $isRemoteFile = true;
            }
        }
        return $isRemoteFile;
    }

    /**
     * Resolves remote XSD includes within the WSDL files.
     *
     * @param string $xml
     * @param string $cacheFile
     * @param unknown_type $parentIsRemote
     * @return string
     */
    private function resolveXsdIncludes($xml, $cacheFile, $parentFile = null)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace(Helper::PFX_XML_SCHEMA, Helper::NS_XML_SCHEMA);
        $query = './/' . Helper::PFX_XML_SCHEMA . ':include';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $schemaLocation = $node->getAttribute('schemaLocation');
                if ($this->isRemoteFile($schemaLocation)) {
                    $schemaLocation = $this->download($schemaLocation);
                    $node->setAttribute('schemaLocation', $schemaLocation);
                } elseif (!is_null($parentFile)) {
                    $schemaLocation = $this->resolveRelativePathInUrl($parentFile, $schemaLocation);
                    $schemaLocation = $this->download($schemaLocation);
                    $node->setAttribute('schemaLocation', $schemaLocation);
                }
            }
        }
        $doc->save($cacheFile);
    }

    /**
     * Resolves the relative path to base into an absolute.
     *
     * @param string $base
     * @param string $relative
     * @return string
     */
    private function resolveRelativePathInUrl($base, $relative)
    {
        $urlParts = parse_url($base);
        // combine base path with relative path
        if (isset($urlParts['path']) && strpos($relative, '/') === 0) {
            // $relative is absolute path from domain (starts with /)
            $path = $relative;
        } elseif (isset($urlParts['path']) && strrpos($urlParts['path'], '/') === (strlen($urlParts['path']) )) {
            // base path is directory
            $path = $urlParts['path'] . $relative;
        } elseif (isset($urlParts['path'])) {
            // strip filename from base path
            $path = substr($urlParts['path'], 0, strrpos($urlParts['path'], '/')) . '/' . $relative;
        } else {
            // no base path
            $path = '/' . $relative;
        }
        // foo/./bar ==> foo/bar
        $path = preg_replace('~/\./~', '/', $path);
        // remove double slashes
        $path = preg_replace('~/+~', '/', $path);
        // split path by '/'
        $parts = explode('/', $path);
        // resolve /../
        foreach ($parts as $key => $part) {
            if ($part == "..") {
                $keyToDelete = $key-1;
                while ($keyToDelete > 0) {
                    if (isset($parts[$keyToDelete])) {
                        unset($parts[$keyToDelete]);
                        break;
                    } else {
                        $keyToDelete--;
                    }
                }
                unset($parts[$key]);
            }
        }
        return $urlParts['scheme'] . '://' . $urlParts['host'] . implode('/', $parts);
    }
}