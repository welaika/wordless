<?php

namespace Phug\Renderer\Profiler;

use ArrayObject;

class EventList extends ArrayObject
{
    /**
     * @var bool
     */
    private $locked = false;

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @return $this
     */
    public function lock()
    {
        $this->locked = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function unlock()
    {
        $this->locked = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->exchangeArray([]);

        return $this->unlock();
    }
}
