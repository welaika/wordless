<?php

namespace Phug\Util;

interface BooleanSubjectInterface
{
    /**
     * Returns the subject of the current object.
     *
     * @return string
     */
    public function getSubject();

    /**
     * Returns true if the subject of the current object should be considered as a boolean.
     *
     * @return true
     */
    public function hasBooleanSubject();
}
