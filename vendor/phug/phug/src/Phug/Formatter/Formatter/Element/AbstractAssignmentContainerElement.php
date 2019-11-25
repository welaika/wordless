<?php

namespace Phug\Formatter\Element;

use Phug\Formatter\AbstractElement;
use Phug\Formatter\AssignmentContainerInterface;
use SplObjectStorage;

abstract class AbstractAssignmentContainerElement extends AbstractElement implements AssignmentContainerInterface
{
    private $assignments;

    /**
     * Add assignment to the markup.
     *
     * @param AssignmentElement $element
     *
     * @return $this
     */
    public function addAssignment(AssignmentElement $element)
    {
        $element->setContainer($this);
        $this->getAssignments()->attach($element);

        return $this;
    }

    /**
     * Remove an assignment from the markup.
     *
     * @param AssignmentElement $element
     *
     * @return $this
     */
    public function removedAssignment(AssignmentElement $element)
    {
        $this->getAssignments()->detach($element);

        return $this;
    }

    /**
     * Return markup assignments list.
     *
     * @return SplObjectStorage[AssignmentElement]
     */
    public function getAssignments()
    {
        if (!$this->assignments) {
            $this->assignments = new SplObjectStorage();
        }

        return $this->assignments;
    }

    /**
     * Return markup assignments list of a specific name.
     *
     * @param $name
     *
     * @return AssignmentElement[]
     */
    public function getAssignmentsByName($name)
    {
        $result = [];

        foreach ($this->getAssignments() as $assignment) {
            /* @var AssignmentElement $assignment */
            if ($assignment->getName() === $name) {
                $result[] = $assignment;
            }
        }

        return $result;
    }
}
