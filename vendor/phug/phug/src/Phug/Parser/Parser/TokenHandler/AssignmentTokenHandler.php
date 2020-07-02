<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class AssignmentTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof AssignmentToken)) {
            throw new \RuntimeException(
                'You can only pass Assignment tokens to this token handler'
            );
        }

        if (!$state->getCurrentNode()) {
            $state->setCurrentNode($state->createNode(ElementNode::class, $token));
        }

        if (!$state->currentNodeIs([ElementNode::class, MixinCallNode::class])) {
            $state->throwException(
                'Assignments can only happen on elements and mixinCalls',
                0,
                $token
            );
        }

        /** @var AssignmentNode $node */
        $node = $state->createNode(AssignmentNode::class, $token);
        $node->setName($token->getName());

        /** @var ElementNode|MixinCallNode $current */
        $current = $state->getCurrentNode();
        $current->getAssignments()->attach($node);

        if ($state->expectNext([AttributeStartToken::class])) {
            $state->setCurrentNode($node);
            //Will trigger iteration of consecutive attribute tokens
            //in AtttributeStartTokenHandler->handleToken with $node as the target ($currentNode in State)
            $state->handleToken();
            $state->setCurrentNode($current);
        }
    }
}
