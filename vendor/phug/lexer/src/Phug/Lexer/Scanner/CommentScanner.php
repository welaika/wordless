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
        $analyzer->analyze(false);
        $lines = $analyzer->getFlatLines();

        if (end($lines) === '') {
            array_pop($lines);
        }
        $token->setValue(implode("\n", $lines));

        //TODO: As it seems, this is the only TextToken that will actually contain newlines, thus Stat->endToken will
        // end up with a wrong line offset. This is why endToken is not applied at all here and only the start
        // position will be kept
        $token->getSourceLocation()->setOffsetLength(1); //Let it have at least 1 length for debugging
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
