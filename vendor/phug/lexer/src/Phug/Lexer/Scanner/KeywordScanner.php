<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\KeywordToken;

class KeywordScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $keywords = $state->getOption('keyword_names');

        if (count($keywords)) {
            $reader = $state->getReader();

            if ($reader->match(
                '(?<name>[a-zA-Z_][a-zA-Z0-9_]*(?:[:-][a-zA-Z_][a-zA-Z0-9_]*)*)(?![a-zA-Z0-9_:-])(?<value>\N*)'
            )) {
                $name = $reader->getMatch('name');

                if (in_array($name, $keywords)) {
                    $value = $reader->getMatch('value');
                    if (mb_substr($value, 0, 1) === ' ') {
                        $value = mb_substr($value, 1);
                    }
                    $reader->consume(mb_strlen($reader->getMatch(0)));

                    /** @var KeywordToken $token */
                    $token = $state->createToken(KeywordToken::class);
                    $token->setName($name);
                    $token->setValue($value);

                    yield $token;
                }
            }
        }
    }
}
