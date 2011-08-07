<?php

require_once $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY']);
$loader->registerNamespace('Zend', $_SERVER['ZEND']);
$loader->register();

spl_autoload_register(function($class) {
    //if (0 === strpos($class, 'BeSimple\\SoapBundle\\')) {
    if (0 === strpos($class, 'BeSimple\\SoapBundle\\')) {
        $path = __DIR__.'/../'.implode('/', array_slice(explode('\\', $class), 2)).'.php';

        if (file_exists($path)) {
            require_once $path;

            return true;
        }

        return false;
    }
});
