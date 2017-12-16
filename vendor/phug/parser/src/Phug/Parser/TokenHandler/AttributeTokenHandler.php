<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class AttributeTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof AttributeToken)) {
            throw new \RuntimeException(
                'You can only pass attribute tokens to this token handler'
            );
        }

        if (!$state->getCurrentNode()) {
            $state->setCurrentNode($state->createNode(ElementNode::class, $token));
        }

        /** @var AttributeNode $node */
        $node = $state->createNode(AttributeNode::class, $token);
        $name = $token->getName();
        $value = $token->getValue();
        $node->setName($name);
        $node->setValue($value);
        $node->setIsEscaped($token->isEscaped());
        $node->setIsChecked($token->isChecked());
        $node->setIsVariadic($token->isVariadic());

        // Mixin calls and assignments take the first
        // expression set as the name as the value
        if (($value === '' || $value === null) &&
            $state->currentNodeIs([MixinCallNode::class, AssignmentNode::class])
        ) {
            if (!$state->currentNodeIs([MixinCallNode::class]) || !$state->getCurrentNode()->areArgumentsCompleted()) {
                $node->setValue($name);
                $node->setName(null);
            }
        }

        /** @var ElementNode|MixinCallNode $current */
        $current = $state->getCurrentNode();
        $current->getAttributes()->attach($node);
    }
}
