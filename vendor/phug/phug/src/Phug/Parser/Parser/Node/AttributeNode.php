<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\CheckTrait;
use Phug\Util\Partial\EscapeTrait;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\ValueTrait;
use Phug\Util\Partial\VariadicTrait;

class AttributeNode extends Node
{
    use NameTrait;
    use ValueTrait;
    use EscapeTrait;
    use CheckTrait;
    use VariadicTrait;
}
