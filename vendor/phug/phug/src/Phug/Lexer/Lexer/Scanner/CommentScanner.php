<?php

/**
 * @example // comment
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Analyzer\LineAnalyzer;
use Phug\Lexer\State;
use Phug\Lexer\Token\CommentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TextToken;

class CommentScanner extends MultilineScanner
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->peekString('//')) {
            return;
        }

        /** @var CommentToken $token */
        $token = $state->createToken(CommentToken::class);

        $reader->consume();

        if ($reader->peekChar('-')) {
            $reader->consume();
            $token->hide();
        }

        yield $state->endToken($token);

        $line = $reader->readUntilNewLine();
        $lines = $line === '' ? [] : [[$line]];

        /** @var TextToken $token */
        $token = $state->createToken(TextToken::class);

        $analyzer = new LineAnalyzer($state, $reader, $lines);
        $analyzer->disallowInterpolation();
        $analyzer->analyze(false);
        $lines = $analyzer->getFlatLines();

        if (end($lines) === '') {
            array_pop($lines);
        }

        $lines = implode("\n", $lines);
        $token->setValue($lines);
        $token->getSourceLocation()->setOffsetLength(mb_strlen($lines));

        yield $token;

        if ($analyzer->hasNewLine()) {
            yield $state->createToken(NewLineToken::class);

            $state->setLevel($analyzer->getNewLevel());

            while ($state->nextOutdent() !== false) {
                yield $state->createToken(OutdentToken::class);
            }
        }
    }
}
