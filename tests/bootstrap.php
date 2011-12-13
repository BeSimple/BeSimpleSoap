<?php

error_reporting(E_ALL | E_STRICT);

// register silently failing autoloader
spl_autoload_register(function($class) {
    if (0 === strpos($class, 'BeSimple\Tests\\')) {
        $path = __DIR__.'/'.strtr($class, '\\', '/').'.php';
        if (file_exists($path) && is_readable($path)) {
            require_once $path;

            return true;
        }
    } elseif (0 === strpos($class, 'BeSimple\SoapCommon\\')) {
        $path = __DIR__.'/../src/'.strtr($class, '\\', '/').'.php';
        if (file_exists($path) && is_readable($path)) {
            require_once $path;

            return true;
        }
    } elseif (0 === strpos($class, 'ass\XmlSecurity\\')) {
            $path = __DIR__.'/../vendor/XmlSecurity/src/'.strtr($class, '\\', '/').'.php';
        if (file_exists($path) && is_readable($path)) {
            require_once $path;

            return true;
        }
    } elseif (0 === strpos($class, 'vfsStream')) {
        $path = __DIR__.'/../vendor/vfsStream/src/main/php/org/bovigo/vfs/'.$class.'.php';
        if (file_exists($path) && is_readable($path)) {
            require_once $path;

            return true;
        }
    }
});