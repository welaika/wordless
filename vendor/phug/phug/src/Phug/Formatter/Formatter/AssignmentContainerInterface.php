<?php

namespace Phug\Formatter;

use Phug\Formatter\Element\AssignmentElement;

interface AssignmentContainerInterface extends ElementInterface
{
    public function getName();

    public function addAssignment(AssignmentElement $element);

    public function removedAssignment(AssignmentElement $element);

    public function getAssignments();

    public function getAssignmentsByName($name);
}
