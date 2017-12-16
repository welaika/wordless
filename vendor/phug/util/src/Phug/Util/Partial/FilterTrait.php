<?php

namespace Phug\Util\Partial;

/**
 * Class FilterTrait.
 */
trait FilterTrait
{
    /**
     * @var mixed
     */
    private $filter = null;

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }
}
