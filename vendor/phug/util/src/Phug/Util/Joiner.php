<?php

namespace Phug\Util;

use Traversable;

class Joiner
{
    /**
     * @var Traversable
     */
    private $traversable;

    public function __construct(Traversable $traversable)
    {
        $this->traversable = $traversable;
    }

    public function join($glue)
    {
        $result = '';
        $first = true;
        foreach ($this->traversable as $value) {
            if (!$first) {
                $result .= $glue;
            }
            $result .= $value;
            if ($first) {
                $first = false;
            }
        }

        return $result;
    }

    public function mapAndJoin(callable $callee, $glue)
    {
        $result = '';
        $first = true;
        foreach ($this->traversable as $value) {
            if (!$first) {
                $result .= $glue;
            }
            $result .= $callee($value);
            if ($first) {
                $first = false;
            }
        }

        return $result;
    }
}
