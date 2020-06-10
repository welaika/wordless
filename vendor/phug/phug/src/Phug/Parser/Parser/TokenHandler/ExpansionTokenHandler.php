<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class ExpansionTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof ExpansionToken)) {
            throw new \RuntimeException(
                'You can only pass expansion tokens to this token handler'
            );
        }

        if (!$state->getCurrentNode()) {
            $state->throwException(
                'Expansion needs an element to work on',
                0,
                $token
            );
        }

        if ($node = $state->getInterpolationNode()) {
            //Make sure to keep the expansion
            $newNode = $state->createNode(ElementNode::class, $token);
            $newNode->setOuterNode($state->getCurrentNode());
            $state->setCurrentNode($newNode);

            return;
        }

        //Make sure to keep the expansion saved
        if ($state->getOuterNode()) {
            $state->getCurrentNode()->setOuterNode($state->getOuterNode());
        }

        $state->setOuterNode($state->getCurrentNode());
        $state->setCurrentNode(null);
    }
}
