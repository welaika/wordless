<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\ValueTrait;

class KeywordNode extends Node
{
    use NameTrait;
    use ValueTrait;
}
