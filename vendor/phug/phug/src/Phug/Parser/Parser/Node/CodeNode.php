<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\BlockTrait;
use Phug\Util\Partial\TransformableTrait;
use Phug\Util\Partial\ValueTrait;
use Phug\Util\TransformableInterface;

class CodeNode extends Node implements TransformableInterface
{
    use ValueTrait;
    use BlockTrait;
    use TransformableTrait;
}
