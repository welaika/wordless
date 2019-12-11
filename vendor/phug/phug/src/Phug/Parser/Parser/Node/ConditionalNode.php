<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\BooleanSubjectInterface;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\SubjectTrait;

class ConditionalNode extends Node implements BooleanSubjectInterface
{
    use NameTrait;
    use SubjectTrait;

    /**
     * {@inheritdoc}
     */
    public function hasBooleanSubject()
    {
        return true;
    }
}
