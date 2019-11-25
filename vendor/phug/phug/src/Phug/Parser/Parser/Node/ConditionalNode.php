<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\SubjectTrait;

class ConditionalNode extends Node
{
    use NameTrait;
    use SubjectTrait;
}
