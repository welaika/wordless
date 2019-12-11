<?php

namespace Phug;

interface InvokerInterface
{
    /**
     * Invoke callbacks that match the passed event.
     *
     * @param object $event instance of callback input.
     *
     * @return array
     */
    public function invoke($event);
}
