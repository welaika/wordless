<?php

namespace Phug\Formatter\Element;

use Phug\Ast\NodeInterface;
use Phug\Parser\NodeInterface as ParserNode;
use Phug\Util\Partial\CheckTrait;
use Phug\Util\Partial\TransformableTrait;
use Phug\Util\PhpTokenizer;
use Phug\Util\TransformableInterface;

class CodeElement extends AbstractValueElement implements TransformableInterface
{
    use CheckTrait;
    use TransformableTrait;

    /**
     * @var string
     */
    protected $preHook = '';

    /**
     * @var string
     */
    protected $postHook = '';

    /**
     * CodeElement constructor.
     *
     * @param string|ExpressionElement $value
     * @param ParserNode|null          $originNode
     * @param NodeInterface|null       $parent
     * @param array|null               $children
     */
    public function __construct(
        $value = null,
        ParserNode $originNode = null,
        NodeInterface $parent = null,
        array $children = null
    ) {
        parent::__construct($value, $originNode, $parent, $children);

        $this->uncheck();
    }

    protected function getValueTokens()
    {
        static $cache = [];

        $value = $this->getValue();
        if (!isset($cache[$value])) {
            $cache[$value] = PhpTokenizer::getTokens(
                preg_replace('/\s*\{\s*\}$/', '', trim($value))
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

    /**
     * @return string
     */
    public function getPreHook()
    {
        return $this->preHook;
    }

    /**
     * @return string
     */
    public function getPostHook()
    {
        return $this->postHook;
    }

    /**
     * Set the code hooked before the inner element code.
     *
     * @param string $preHook
     *
     * @return $this
     */
    public function setPreHook($preHook)
    {
        $this->preHook = $preHook;

        return $this;
    }

    /**
     * Set the code hooked after the inner element code.
     *
     * @param string $postHook
     *
     * @return $this
     */
    public function setPostHook($postHook)
    {
        $this->postHook = $postHook;

        return $this;
    }

    /**
     * Prepend code before the inner code and the already pre-hooked codes.
     *
     * @param string $code
     *
     * @return $this
     */
    public function prependCode($code)
    {
        return $this->setPreHook($code.$this->getPreHook());
    }

    /**
     * Append code after the inner code and the already post-hooked codes.
     *
     * @param string $code
     *
     * @return $this
     */
    public function appendCode($code)
    {
        return $this->setPostHook($this->getPostHook().$code);
    }
}
