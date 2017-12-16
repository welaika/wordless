<?php

namespace Phug\Util\Partial;

/**
 * Class CheckTrait.
 */
trait CheckTrait
{
    /**
     * @var bool
     */
    private $checked = true;

    /**
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     *
     * @return $this
     */
    public function setIsChecked($checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * @return $this
     */
    public function check()
    {
        $this->checked = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function uncheck()
    {
        $this->checked = false;

        return $this;
    }
}
