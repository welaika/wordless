<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\IdToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class IdTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof IdToken)) {
            throw new \RuntimeException(
                'You can only pass id tokens to this token handler'
            );
        }

        if (!$state->getCurrentNode()) {
            $state->setCurrentNode($state->createNode(ElementNode::class, $token));
        }

        if (!$state->currentNodeIs([ElementNode::class, MixinCallNode::class])) {
            $state->throwException(
                'IDs can only be used on elements and mixin calls',
                0,
                $token
            );
        }

        /** @var AttributeNode $attr */
        $attr = $state->createNode(AttributeNode::class, $token);
        $attr->setName('id');
        $attr->setValue(var_export($token->getName(), true));
        $attr->unescape()->uncheck();

        /** @var ElementNode|MixinCallNode $current */
        $current = $state->getCurrentNode();
        $current->getAttributes()->attach($attr);
    }
}
