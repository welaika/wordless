<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\State;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TextToken;

class MarkupScanner extends MultilineScanner
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->peekChar('<')) {
            return;
        }

        $level = $state->getLevel();
        $lines = [];

        $newLine = false;
        while ($reader->hasLength()) {
            $newLine = true;
            $indentationScanner = new IndentationScanner();
            $newLevel = $indentationScanner->getIndentLevel($state, $level);

            if (!$reader->peekChars(['<', ' ', "\t", "\n"])) {
                break;
            }

            if ($newLevel < $level) {
                if ($reader->match('[ \t]*\n')) {
                    $reader->consume(mb_strlen($reader->getMatch(0)));
                    $lines[] = [];

                    continue;
                }

                $state->setLevel($newLevel);

                break;
            }

            $line = [];

            foreach ($state->scan(InterpolationScanner::class) as $subToken) {
                $line[] = $subToken instanceof TextToken ? $subToken->getValue() : $subToken;
            }

            $line[] = $reader->readUntilNewLine();
            $lines[] = $line;

            if ($newLine = $reader->peekNewLine()) {
                $reader->consume(1);
            }
        }

        foreach ($this->getUnescapedLines($state, $lines) as $token) {
            yield $token;
        }

        if ($newLine) {
            yield $state->createToken(NewLineToken::class);
        }
    }
}
