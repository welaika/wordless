<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\CheckTrait;
use Phug\Util\Partial\EscapeTrait;
use Phug\Util\Partial\ValueTrait;

class ExpressionNode extends Node
{
    use ValueTrait;
    use EscapeTrait;
    use CheckTrait;
}
