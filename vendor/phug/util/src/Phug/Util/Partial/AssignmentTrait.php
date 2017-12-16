<?php

namespace Phug\Util\Partial;

use SplObjectStorage;

/**
 * Class AssignmentTrait.
 */
trait AssignmentTrait
{
    /**
     * @var SplObjectStorage
     */
    private $assignments = null;

    /**
     * @return SplObjectStorage
     */
    public function getAssignments()
    {
        if (!$this->assignments) {
            $this->assignments = new SplObjectStorage();
        }

        return $this->assignments;
    }
}
