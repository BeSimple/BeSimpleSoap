<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Annotation;

/**
 * Based on \Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
interface ConfigurationInterface
{
    /**
     * Returns the alias name for an annotated configuration.
     *
     * @return string
     */
    function getAliasName();
}