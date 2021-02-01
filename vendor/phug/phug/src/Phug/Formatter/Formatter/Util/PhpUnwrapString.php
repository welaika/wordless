<?php

namespace Phug\Formatter\Util;

class PhpUnwrapString
{
    /**
     * @var string
     */
    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public static function withoutOpenTag($code)
    {
        $unwrappedCode = new self($code);
        $unwrappedCode->unwrapStart();
        $unwrappedCode->movePhugCodeAfterNamespace();

        return $unwrappedCode;
    }

    public function movePhugCodeAfterNamespace()
    {
        $this->code = preg_replace(
            '/^((?:[ \t]*(?:\\\\Phug\\\\.*\n|\\/\\/.*\n|(?:\?><\?php)?[ \t\n]+)?)*)(namespace (?:.*)\n)/',
            '$2$1',
            $this->code
        );
    }

    public function unwrapStart()
    {
        $this->code = preg_match('/^<\?php\s/', $this->code)
            ? mb_substr($this->code, 6)
            : '?>'.$this->code;
    }

    public function unwrapEnd()
    {
        $this->code = preg_match('/\s\?>$/', $this->code) && strpos($this->code, '<?=') === false
            ? mb_substr($this->code, 0, -3).';'
            : $this->code.'<?php';
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
