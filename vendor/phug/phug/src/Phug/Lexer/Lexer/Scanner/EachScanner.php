<?php

/**
 * @example each $value, $key in $items
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Scanner\Partial\NamespaceAndTernaryTrait;
use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\EachToken;

class EachScanner implements ScannerInterface
{
    use NamespaceAndTernaryTrait;

    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->match('each[\t ]+')) {
            return;
        }

        /** @var EachToken $token */
        $token = $state->createToken(EachToken::class);
        $reader->consume();

        if (!$reader->match(
            "\\$?(?<itemName>[a-zA-Z_][a-zA-Z0-9_]*)(?:[\t ]*,[\t ]*".
            "\\$?(?<keyName>[a-zA-Z_][a-zA-Z0-9_]*))?[\t ]+in[\t ]+"
        )) {
            $state->throwException(
                'The syntax for each is `each $itemName[, $keyName]] in {{subject}}`'
            );
        }

        $token->setItem($reader->getMatch('itemName'));
        $token->setKey($reader->getMatch('keyName'));

        $reader->consume();

        $subject = $this->checkForNamespaceAndTernary($reader);

        if ($subject === '') {
            $state->throwException(
                '`each`-statement has no subject to operate on'
            );
        }

        $token->setSubject($subject);

        yield $state->endToken($token);
    }
}
