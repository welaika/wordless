<?php

namespace Phug\Formatter\Element;

use Phug\Ast\NodeInterface;
use Phug\Formatter\AbstractElement;
use Phug\Parser\NodeInterface as ParserNode;

class VariableElement extends AbstractElement
{
    /**
     * @var CodeElement
     */
    protected $variable;

    /**
     * @var ExpressionElement
     */
    protected $expression;

    /**
     * VariableElement constructor.
     *
     * @param CodeElement|null       $variable
     * @param ExpressionElement|null $expression
     * @param ParserNode|null        $originNode
     * @param NodeInterface|null     $parent
     * @param array|null             $children
     */
    public function __construct(
        CodeElement $variable = null,
        ExpressionElement $expression = null,
        ParserNode $originNode = null,
        NodeInterface $parent = null,
        array $children = null
    ) {
        parent::__construct($originNode, $parent, $children);

        if ($variable) {
            $this->setVariable($variable);
        }

        if ($expression) {
            $this->setExpression($expression);
        }
    }

    public function setVariable(CodeElement $variable)
    {
        $this->variable = $variable;
    }

    public function setExpression(ExpressionElement $expression)
    {
        $this->expression = $expression;
    }

    public function getVariable()
    {
        return $this->variable;
    }

    public function getExpression()
    {
        return $this->expression;
    }
}
