<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\State;
use Phug\Lexer\Token\ConditionalToken;

class ConditionalScanner extends ControlStatementScanner
{
    public function __construct()
    {
        parent::__construct(
            ConditionalToken::class,
            ['if', 'unless', 'else[ \t]*if', 'else']
        );
    }

    public function scan(State $state)
    {
        foreach (parent::scan($state) as $token) {
            if ($token instanceof ConditionalToken) {
                //Make sure spaces are replaced from `elseif`/`else if` to make a final keyword, "elseif"
                $token->setName(preg_replace('/[ \t]/', '', $token->getName()));

                if ($token->getName() === 'else' && !in_array($token->getSubject(), ['', false, null])) {
                    $state->throwException(
                        'The `else`-conditional statement can\'t have a subject'
                    );
                }
            }

            yield $token;
        }
    }
}
