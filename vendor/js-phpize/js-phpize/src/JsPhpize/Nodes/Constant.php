<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

/**
 * Class Constant.
 *
 * @property-read string $value    raw value
 * @property-read string $type     constant type
 * @property-read bool   $dotChild rather is constant is used as an object child (dot operator, e.g. `->` for PHP)
 */
class Constant extends Value implements Assignable
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $dotChild;

    /**
     * Constant constructor.
     *
     * @param $type
     * @param $value
     * @param $dotChild
     *
     * @throws Exception
     */
    public function __construct($type, $value, $dotChild = false)
    {
        if (!in_array($type, ['constant', 'number', 'string', 'regexp'])) {
            throw new Exception("The given type [$type] is not a valid constant type.", 23);
        }

        $this->type = $type;
        $this->value = $value;
        $this->dotChild = $dotChild;
    }

    public function getNonAssignableReason()
    {
        if ($this->type !== 'constant') {
            return "{$this->type} is not assignable.";
        }

        if (in_array($this->value, ['NAN', 'INF'])) {
            return "{$this->value} is not assignable.";
        }

        if (mb_substr($this->value, 0, 2) === 'M_') {
            return "'M_' prefix is reserved to mathematical constants.";
        }

        return false;
    }
}
