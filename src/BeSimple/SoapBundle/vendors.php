#!/usr/bin/env php
<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/*

CAUTION: This file installs the dependencies needed to run the BeSimpleSoapBundle test suite.

https://github.com/BeSimple/BeSimpleSoapBundle

*/

if (!is_dir($vendorDir = dirname(__FILE__).'/vendor')) {
    mkdir($vendorDir, 0777, true);
}

$deps = array(
    array('symfony', 'http://github.com/symfony/symfony.git', 'origin/HEAD'),
    array('zend-framework/library/Zend/Soap', 'http://github.com/BeSimple/zend-soap.git', 'origin/HEAD'),
    array('zend-framework/library/Zend/Mime', 'http://github.com/BeSimple/zend-mime.git', 'origin/HEAD'),
);

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    }

    system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
}
