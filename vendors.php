#!/usr/bin/env php
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

/*

CAUTION: This file installs the dependencies needed to run the BeSimpleSoapClient test suite.

https://github.com/BeSimple/BeSimpleSoapClient

*/

if (!is_dir($vendorDir = dirname(__FILE__).'/vendor')) {
    mkdir($vendorDir, 0777, true);
}

$deps = array(
    array('besimple-soapcommon', 'http://github.com/BeSimple/BeSimpleSoapCommon.git', 'origin/HEAD'),
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
