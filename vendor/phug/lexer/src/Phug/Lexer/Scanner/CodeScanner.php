<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\CodeToken;
use Phug\Lexer\Token\TextToken;

class CodeScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->match('-[ \t]*')) {
            return;
        }

        /** @var CodeToken $token */
        $token = $state->createToken(CodeToken::class);

        $reader->consume();

        //Single-line code
        foreach ($state->scan(TextScanner::class) as $textToken) {
            //Trim the text as expressions usually would
            yield $state->endToken($token);

            if ($textToken instanceof TextToken) {
                $textToken->setValue(trim($textToken->getValue()));
                yield $textToken;
            }

            return;
        }

        //Multi-line code
        $token->setIsBlock(true);
        yield $state->endToken($token);

        foreach ($state->scan(MultilineScanner::class) as $token) {
            yield $token;
        }
    }
}
