<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\EventListener;

use Bundle\WebServiceBundle\ServiceDefinition\Annotation\ConfigurationInterface;

use Doctrine\Common\Annotations\Reader;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Based on \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener
 *
 * @author Francis Besset <francis.besset@gmail.com>
 */
class ControllerListener
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $reader;

    /**
     * Constructor.
     *
     * @param Reader $reader An Reader instance
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Modifies the Request object to apply configuration information found in
     * controllers annotations like the template to render or HTTP caching
     * configuration.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        $request = $event->getRequest();
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof ConfigurationInterface) {
                $request->attributes->set('_'.$configuration->getAliasName(), $configuration);
            }
        }
    }
}