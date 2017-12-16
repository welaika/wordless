<?php

namespace Phug\Formatter\Util;

use Phug\Formatter;

class PhpUnwrap
{
    /**
     * @var string
     */
    private $code;

    public function __construct($element, Formatter $formatter)
    {
        $elements = is_array($element) ? $element : [$element];
        $code = implode('', array_map(function ($child) use ($formatter) {
            return is_string($child) ? $child : $formatter->format($child);
        }, $elements));
        $code = preg_match('/^<\?php\s/', $code)
            ? mb_substr($code, 6)
            : '?>'.$code;
        $code = preg_match('/\s\?>$/', $code) && strpos($code, '<?=') === false
            ? mb_substr($code, 0, -3).';'
            : $code.'<?php';

        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    public function __toString()
    {
        return $this->getCode();
    }
}
