<?php

namespace Phug\Formatter\Event;

use Phug\Event;
use Phug\FormatterEvent;

class DependencyStorageEvent extends Event
{
    private $dependencyStorage;

    /**
     * DependencyStorageEvent constructor.
     *
     * @param string $dependencyStorage
     */
    public function __construct($dependencyStorage)
    {
        parent::__construct(FormatterEvent::DEPENDENCY_STORAGE);

        $this->dependencyStorage = $dependencyStorage;
    }

    /**
     * @return string
     */
    public function getDependencyStorage()
    {
        return $this->dependencyStorage;
    }

    /**
     * @param string $dependencyStorage
     */
    public function setDependencyStorage($dependencyStorage)
    {
        $this->dependencyStorage = $dependencyStorage;
    }
}
