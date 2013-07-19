<?php

/*
 * This file is part of the BeSimpleSoapClient.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapClient;

use BeSimple\SoapCommon\Helper;

/**
 * Downloads WSDL files with cURL. Uses the WSDL_CACHE_* constants and the
 * 'soap.wsdl_*' ini settings. Does only file caching as SoapClient only
 * supports a file name parameter. The class also resolves remote XML schema
 * includes.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class WsdlDownloader
{
    /**
     * Cache enabled.
     *
     * @var bool
     */
    protected $cacheEnabled;

    /**
     * Cache dir.
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Cache TTL.
     *
     * @var int
     */
    protected $cacheTtl;

    /**
     * cURL instance for downloads.
     *
     * @var unknown_type
     */
    protected $curl;

    /**
     * Resolve WSDl/XSD includes.
     *
     * @var boolean
     */
    protected $resolveRemoteIncludes = true;

    /**
     * Constructor.
     *
     * @param \BeSimple\SoapClient\Curl $curl                  Curl instance
     * @param boolean                   $resolveRemoteIncludes WSDL/XSD include enabled?
     * @param boolean                   $cacheWsdl             Cache constant
     */
    public function __construct(Curl $curl, $resolveRemoteIncludes = true, $cacheWsdl = WSDL_CACHE_DISK)
    {
        $this->curl = $curl;
        $this->resolveRemoteIncludes = $resolveRemoteIncludes;
        // get current WSDL caching config
        $this->cacheEnabled = (bool) ini_get('soap.wsdl_cache_enabled');
        if ($this->cacheEnabled === true
            && $cacheWsdl === WSDL_CACHE_NONE) {
            $this->cacheEnabled = false;
        }
        $this->cacheDir = ini_get('soap.wsdl_cache_dir');
        if (!is_dir($this->cacheDir)) {
            $this->cacheDir = sys_get_temp_dir();
        }
        $this->cacheDir = rtrim($this->cacheDir, '/\\');
        $this->cacheTtl = ini_get('soap.wsdl_cache_ttl');
    }

    /**
     * Download given WSDL file and return name of cache file.
     *
     * @param string $wsdl WSDL file URL/path
     *
     * @return string
     */
    public function download($wsdl)
    {
        // download and cache remote WSDL files or local ones where we want to
        // resolve remote XSD includes
        $isRemoteFile = $this->isRemoteFile($wsdl);
        if ($isRemoteFile === true || $this->resolveRemoteIncludes === true) {
            $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'wsdl_' . md5($wsdl) . '.cache';
            if ($this->cacheEnabled === false
                || !file_exists($cacheFile)
                || (filemtime($cacheFile) + $this->cacheTtl) < time()) {
                if ($isRemoteFile === true) {
                    // execute request
                    $responseSuccessfull = $this->curl->exec($wsdl);
                    // get content
                    if ($responseSuccessfull === true) {
                        $response = $this->curl->getResponseBody();
                        if ($this->resolveRemoteIncludes === true) {
                            $this->resolveRemoteIncludes($response, $cacheFile, $wsdl);
                        } else {
                            file_put_contents($cacheFile, $response);
                        }
                    } else {
                        throw new \ErrorException("SOAP-ERROR: Parsing WSDL: Couldn't load from '" . $wsdl ."'");
                    }
                } elseif (file_exists($wsdl)) {
                    $response = file_get_contents($wsdl);
                    $this->resolveRemoteIncludes($response, $cacheFile);
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
     * @param string $file File URL/path
     *
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
     * Resolves remote WSDL/XSD includes within the WSDL files.
     *
     * @param string  $xml        XML file
     * @param string  $cacheFile  Cache file name
     * @param boolean $parentFile Parent file name
     *
     * @return void
     */
    private function resolveRemoteIncludes($xml, $cacheFile, $parentFile = null)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace(Helper::PFX_XML_SCHEMA, Helper::NS_XML_SCHEMA);
        $xpath->registerNamespace(Helper::PFX_WSDL, Helper::NS_WSDL);
        // WSDL include/import
        $query = './/' . Helper::PFX_WSDL . ':include | .//' . Helper::PFX_WSDL . ':import';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $location = $node->getAttribute('location');
                if ($this->isRemoteFile($location)) {
                    $location = $this->download($location);
                    $node->setAttribute('location', $location);
                } elseif (!is_null($parentFile)) {
                    $location = $this->resolveRelativePathInUrl($parentFile, $location);
                    $location = $this->download($location);
                    $node->setAttribute('location', $location);
                }
            }
        }
        // XML schema include/import
        $query = './/' . Helper::PFX_XML_SCHEMA . ':include | .//' . Helper::PFX_XML_SCHEMA . ':import';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                if ($node->hasAttribute('schemaLocation')) {
                    $schemaLocation = $node->getAttribute('schemaLocation');
                    if ($this->isRemoteFile($schemaLocation)) {
                        $schemaLocation = $this->download($schemaLocation);
                        $node->setAttribute('schemaLocation', $schemaLocation);
                    } elseif (null !== $parentFile) {
                        $schemaLocation = $this->resolveRelativePathInUrl($parentFile, $schemaLocation);
                        $schemaLocation = $this->download($schemaLocation);
                        $node->setAttribute('schemaLocation', $schemaLocation);
                    }
                }
            }
        }
        $doc->save($cacheFile);
    }

    /**
     * Resolves the relative path to base into an absolute.
     *
     * @param string $base     Base path
     * @param string $relative Relative path
     *
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
        $hostname = $urlParts['scheme'] . '://' . $urlParts['host'];
        if (isset($urlParts['port'])) {
            $hostname .= ':' . $urlParts['port'];
        }

        return $hostname . implode('/', $parts);
    }
}