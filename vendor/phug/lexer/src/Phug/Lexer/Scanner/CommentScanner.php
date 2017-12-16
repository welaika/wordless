<?php

namespace Phug\Lexer\Scanner;

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
        $level = $state->getLevel();
        $line = $reader->readUntilNewLine();
        $lines = $line === '' ? [] : [$line];

        /** @var TextToken $token */
        $token = $state->createToken(TextToken::class);

        $newLine = false;
        while ($reader->hasLength()) {
            $newLine = true;
            $indentationScanner = new IndentationScanner();
            $newLevel = $indentationScanner->getIndentLevel($state, $level);

            if (!$reader->peekChars([' ', "\t", "\n"])) {
                break;
            }

            if ($newLevel < $level) {
                if ($reader->match('[ \t]*\n')) {
                    $reader->consume(mb_strlen($reader->getMatch(0)));
                    $lines[] = '';

                    continue;
                }

                $state->setLevel($newLevel);

                break;
            }

            $lines[] = $reader->readUntilNewLine();

            if ($newLine = $reader->peekNewLine()) {
                $reader->consume(1);
            }
        }

        if (end($lines) === '') {
            array_pop($lines);
        }
        $token->setValue(implode("\n", $lines));

        //TODO: As it seems, this is the only TextToken that will actually contain newlines, thus Stat->endToken will
        // end up with a wrong line offset. This is why endToken is not applied at all here and only the start
        // position will be kept
        $token->getSourceLocation()->setOffsetLength(1); //Let it have at least 1 length for debugging
        yield $token;

        if ($newLine) {
            yield $state->createToken(NewLineToken::class);

            if (isset($newLevel)) {
                $state->setLevel($newLevel);

                while ($state->nextOutdent() !== false) {
                    yield $state->createToken(OutdentToken::class);
                }
            }
        }
    }
}
