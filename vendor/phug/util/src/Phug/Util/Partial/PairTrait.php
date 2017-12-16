<?php

namespace Phug\Util\Partial;

/**
 * Class PairTrait.
 */
trait PairTrait
{
    /**
     * @var string
     */
    private $item = null;
    /**
     * @var string
     */
    private $key = null;

    /**
     * @return string
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param string $itemName
     *
     * @return $this
     */
    public function setItem($itemName)
    {
        $this->item = $itemName;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $keyName
     *
     * @return $this
     */
    public function setKey($keyName)
    {
        $this->key = $keyName;

        return $this;
    }
}
