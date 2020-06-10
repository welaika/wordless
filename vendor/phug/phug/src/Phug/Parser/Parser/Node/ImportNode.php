<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\FilterTrait;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\PathTrait;

class ImportNode extends Node
{
    use NameTrait;
    use PathTrait;
    use FilterTrait;
}
