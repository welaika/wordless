<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\CodeToken;
use Phug\Lexer\Token\TextToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class CodeTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof CodeToken)) {
            throw new \RuntimeException(
                'You can only pass code tokens to this token handler'
            );
        }

        /** @var CodeNode $node */
        $node = $state->createNode(CodeNode::class, $token);

        if ($state->getCurrentNode()) {
            $token = $state->expectNext([TextToken::class]);
            if (!$token) {
                $state->throwException(
                    'Unexpected token `blockcode` expected `text`, `interpolated-code` or `code`',
                    0,
                    $token
                );
            }
            $node->setValue($token->getValue());
        }

        $state->append($node);
    }
}
