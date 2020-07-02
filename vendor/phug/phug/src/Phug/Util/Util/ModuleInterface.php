<?php

namespace Phug\Util;

/**
 * Interface ModuleInterface.
 */
interface ModuleInterface extends OptionInterface
{
    public function getContainer();

    public function attachEvents();

    public function detachEvents();
}
