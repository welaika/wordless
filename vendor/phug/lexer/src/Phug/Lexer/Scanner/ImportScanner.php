<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\ImportToken;
use Phug\Lexer\Token\TextToken;

class ImportScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $tokens = [];

        /** @var ImportToken $token */
        foreach ($state->scanToken(
            ImportToken::class,
            '(?<name>extend|include)s?(?= |:)'
        ) as $token) {
            $tokens[] = $token;

            $reader = $state->getReader();

            if ($reader->match('[\t ]+(?<path>[a-zA-Z0-9_\\\\\\/. -]+)')) {
                $token->setPath($reader->getMatch('path'));
                $reader->consume();

                break;
            }

            foreach ($state->scan(FilterScanner::class) as $subToken) {
                if ($subToken instanceof TextToken) {
                    $token->setPath($subToken->getValue());

                    break;
                }

                $tokens[] = $subToken;
            }

            break;
        }

        foreach ($tokens as $token) {
            yield $token;
        }
    }
}
