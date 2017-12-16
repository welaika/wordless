<?php

namespace Phug\Formatter\Element;

use Phug\Formatter\Partial\TransformableTrait;

class CodeElement extends AbstractValueElement
{
    use TransformableTrait;

    protected function getValueTokens()
    {
        static $cache = [];

        $value = $this->getValue();
        if (!isset($cache[$value])) {
            $cache[$value] = array_slice(
                token_get_all(
                    '<?php '.
                    preg_replace('/\s*\{\s*\}$/', '', trim($value))
                ),
                1
            );
        }

        return $cache[$value];
    }

    public function isCodeBlockOpening()
    {
        $tokens = $this->getValueTokens();

        return isset($tokens[0]) &&
            is_array($tokens[0]) &&
            in_array($tokens[0][0], [
                T_CATCH,
                T_CLASS,
                T_DO,
                T_ELSE,
                T_ELSEIF,
                T_EXTENDS,
                T_FINALLY,
                T_FOR,
                T_FOREACH,
                T_FUNCTION,
                T_IF,
                T_IMPLEMENTS,
                T_INTERFACE,
                T_NAMESPACE,
                T_SWITCH,
                T_TRAIT,
                T_TRY,
                T_WHILE,
            ]);
    }

    public function hasBlockContent()
    {
        $tokens = $this->getValueTokens();

        return end($tokens) === '}' || $this->hasChildren();
    }

    public function isCodeBlock()
    {
        return $this->isCodeBlockOpening() && $this->hasBlockContent();
    }

    public function needAccolades()
    {
        $tokens = $this->getValueTokens();

        return (
            $this->hasChildren() || (
                $this->isCodeBlockOpening() &&
                !$this->hasBlockContent()
            )
        ) && !in_array(end($tokens), [';', '{']);
    }
}
