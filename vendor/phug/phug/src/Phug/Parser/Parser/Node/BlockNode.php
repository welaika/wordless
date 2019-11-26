<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\ModeTrait;
use Phug\Util\Partial\NameTrait;

class BlockNode extends Node
{
    use NameTrait;
    use ModeTrait;
}
