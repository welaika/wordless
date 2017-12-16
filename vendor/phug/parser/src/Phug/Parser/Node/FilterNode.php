<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;

class FilterNode extends Node
{
    use NameTrait;
    use AttributeTrait;

    /**
     * @var ImportNode
     */
    protected $import;

    /**
     * @return ImportNode
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param ImportNode $import
     */
    public function setImport($import)
    {
        $this->import = $import;
    }
}
