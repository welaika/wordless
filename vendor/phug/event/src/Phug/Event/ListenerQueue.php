<?php

namespace Phug\Event;

class ListenerQueue extends \SplPriorityQueue
{
    public function compare($priority, $priorityToCompare)
    {
        if ($priority === $priorityToCompare) {
            return 0;
        }

        return $priority > $priorityToCompare ? -1 : 1;
    }

    public function insert($value, $priority)
    {
        if (!is_callable($value)) {
            $previous = null;

            try {
                if (is_array($value) || $value instanceof \Traversable) {
                    return $this->insertMultiple($value, $priority);
                }
            } catch (\InvalidArgumentException $multipleInsertException) {
                $previous = $multipleInsertException;
            }

            throw new \InvalidArgumentException(
                'Callback inserted into ListenerQueue needs to be callable',
                1,
                $previous
            );
        }

        parent::insert($value, $priority);
    }

    public function insertMultiple($value, $priority)
    {
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new \InvalidArgumentException(
                'insertMultiple only accept array or Traversable as first argument',
                2
            );
        }

        foreach ($value as $callback) {
            $this->insert($callback, $priority);
        }
    }
}
