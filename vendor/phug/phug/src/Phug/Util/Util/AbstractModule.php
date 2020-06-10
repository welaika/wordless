<?php

namespace Phug\Util;

use Phug\Util\Partial\OptionTrait;

/**
 * Abstract class AbstractModule.
 */
abstract class AbstractModule implements ModuleInterface
{
    use OptionTrait;

    private $container;

    public function __construct(ModuleContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ModuleContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function getEventListeners()
    {
        return [];
    }

    public function attachEvents()
    {
        foreach ($this->getEventListeners() as $event => $listener) {
            $this->container->attach($event, $listener);
        }
    }

    public function detachEvents()
    {
        foreach ($this->getEventListeners() as $event => $listener) {
            $this->container->detach($event, $listener);
        }
    }
}
