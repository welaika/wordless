<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\PairTrait;
use Phug\Util\Partial\SubjectTrait;

class EachNode extends Node
{
    use SubjectTrait;
    use PairTrait;
}
