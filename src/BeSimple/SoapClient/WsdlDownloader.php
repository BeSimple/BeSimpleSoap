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

use BeSimple\SoapCommon\Cache;
use BeSimple\SoapCommon\Helper;
use Symfony\Component\HttpFoundation\Response;

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
    const XML_MIN_LENGTH = 25;

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
    public function __construct(Curl $curl, $resolveRemoteIncludes = true, $cacheWsdl = Cache::TYPE_DISK)
    {
        $this->curl                  = $curl;
        $this->resolveRemoteIncludes = (Boolean) $resolveRemoteIncludes;

        // get current WSDL caching config
        $this->cacheEnabled = $cacheWsdl === Cache::TYPE_NONE ? Cache::DISABLED : Cache::ENABLED == Cache::isEnabled();
        $this->cacheDir = Cache::getDirectory();
        $this->cacheTtl = Cache::getLifetime();
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
        if ($isRemoteFile || $this->resolveRemoteIncludes) {
            $cacheFilePath = $this->cacheDir.DIRECTORY_SEPARATOR.'wsdl_'.md5($wsdl).'.cache';

            if (!$this->cacheEnabled || !file_exists($cacheFilePath) || (filemtime($cacheFilePath) + $this->cacheTtl) < time()) {
                if ($isRemoteFile) {
                    // execute request
                    $this->curl->exec($wsdl);
                    // get content
                    if (Response::HTTP_OK === $this->curl->getResponseStatusCode()) {
                        $response = $this->curl->getResponseBody();
                        if (empty($response)) {
                            throw new \ErrorException("SOAP-ERROR: Parsing WSDL: Got empty wsdl from '" . $wsdl ."'");
                        }

                        if ($this->resolveRemoteIncludes) {
                            $this->resolveRemoteIncludes($response, $cacheFilePath, $wsdl);
                        } else {
                            file_put_contents($cacheFilePath, $response);
                        }
                    } else {
                        throw new \ErrorException("SOAP-ERROR: Parsing WSDL: Unexpected response code received from '" . $wsdl ."', response code: " . $this->curl->getResponseStatusCode());
                    }
                } elseif (file_exists($wsdl)) {
                    $response = file_get_contents($wsdl);
                    $this->resolveRemoteIncludes($response, $cacheFilePath);
                } else {
                    throw new \ErrorException("SOAP-ERROR: Parsing WSDL: Couldn't load from '" . $wsdl ."'");
                }
            }

            return $cacheFilePath;
        } elseif (file_exists($wsdl)) {
            return realpath($wsdl);
        }

        throw new \ErrorException("SOAP-ERROR: Parsing WSDL: Couldn't load from '" . $wsdl ."'");
    }

    /**
     * Do we have a remote file?
     *
     * @param string $file File URL/path
     *
     * @return boolean
     */
    protected function isRemoteFile($file)
    {
        // @parse_url to suppress E_WARNING for invalid urls
        if (false !== $url = @parse_url($file)) {
            if (isset($url['scheme']) && 'http' === substr($url['scheme'], 0, 4)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolves remote WSDL/XSD includes within the WSDL files.
     *
     * @param string  $xml            XML file
     * @param string  $cacheFilePath  Cache file name
     * @param boolean $parentFilePath Parent file name
     *
     * @return void
     */
    protected function resolveRemoteIncludes($xml, $cacheFilePath, $parentFilePath = null)
    {
        $doc = new \DOMDocument();
        $parsedOk = $doc->loadXML($xml);
        if (!$parsedOk) {
            throw new \RuntimeException("SOAP-ERROR: Couldn't parse xml: $xml");
        }

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace(Helper::PFX_XML_SCHEMA, Helper::NS_XML_SCHEMA);
        $xpath->registerNamespace(Helper::PFX_WSDL, Helper::NS_WSDL);

        // WSDL include/import
        $query = './/'.Helper::PFX_WSDL.':include | .//'.Helper::PFX_WSDL.':import';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $location = $node->getAttribute('location');
                if ($this->isRemoteFile($location)) {
                    $location = $this->download($location);
                    $node->setAttribute('location', $location);
                } elseif (null !== $parentFilePath) {
                    $location = $this->resolveRelativePathInUrl($parentFilePath, $location);
                    $location = $this->download($location);
                    $node->setAttribute('location', $location);
                }
            }
        }

        // XML schema include/import
        $query = './/'.Helper::PFX_XML_SCHEMA.':include | .//'.Helper::PFX_XML_SCHEMA.':import';
        $nodes = $xpath->query($query);
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                if ($node->hasAttribute('schemaLocation')) {
                    $schemaLocation = $node->getAttribute('schemaLocation');
                    if ($this->isRemoteFile($schemaLocation)) {
                        $schemaLocation = $this->download($schemaLocation);
                        $node->setAttribute('schemaLocation', $schemaLocation);
                    } elseif (null !== $parentFilePath) {
                        $schemaLocation = $this->resolveRelativePathInUrl($parentFilePath, $schemaLocation);
                        $schemaLocation = $this->download($schemaLocation);
                        $node->setAttribute('schemaLocation', $schemaLocation);
                    }
                }
            }
        }

        $xmlResolved = $doc->saveXML();

        if (empty($xmlResolved) || strlen($xmlResolved) < self::XML_MIN_LENGTH) {
            throw new \RuntimeException("SOAP-ERROR: Detected empty wsdl in: $cacheFilePath for xml: $xml");
        }

        file_put_contents($cacheFilePath, $xmlResolved);
    }

    /**
     * Resolves the relative path to base into an absolute.
     *
     * @param string $base     Base path
     * @param string $relative Relative path
     *
     * @return string
     */
    protected function resolveRelativePathInUrl($base, $relative)
    {
        $urlParts = parse_url($base);

        // combine base path with relative path
        if (isset($urlParts['path']) && '/' === $relative{0}) {
            // $relative is absolute path from domain (starts with /)
            $path = $relative;
        } elseif (isset($urlParts['path']) && strrpos($urlParts['path'], '/') === (strlen($urlParts['path']) )) {
            // base path is directory
            $path = $urlParts['path'].$relative;
        } elseif (isset($urlParts['path'])) {
            // strip filename from base path
            $path = substr($urlParts['path'], 0, strrpos($urlParts['path'], '/')).'/'.$relative;
        } else {
            // no base path
            $path = '/'.$relative;
        }

        // foo/./bar ==> foo/bar
        // remove double slashes
        $path = preg_replace(array('#/\./#', '#/+#'), '/', $path);

        // split path by '/'
        $parts = explode('/', $path);

        // resolve /../
        foreach ($parts as $key => $part) {
            if ('..' === $part) {
                $keyToDelete = $key - 1;

                while ($keyToDelete > 0) {
                    if (isset($parts[$keyToDelete])) {
                        unset($parts[$keyToDelete]);

                        break;
                    }

                    $keyToDelete--;
                }

                unset($parts[$key]);
            }
        }

        $hostname = $urlParts['scheme'].'://'.$urlParts['host'];
        if (isset($urlParts['port'])) {
            $hostname .= ':'.$urlParts['port'];
        }

        return $hostname.implode('/', $parts);
    }
}
