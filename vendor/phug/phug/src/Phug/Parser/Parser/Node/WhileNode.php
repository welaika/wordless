<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\BooleanSubjectInterface;
use Phug\Util\Partial\SubjectTrait;

class WhileNode extends Node implements BooleanSubjectInterface
{
    use SubjectTrait;

    /**
     * {@inheritdoc}
     */
    public function hasBooleanSubject()
    {
        return true;
    }
}
