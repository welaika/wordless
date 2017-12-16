<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TextToken;

class MultilineScanner implements ScannerInterface
{
    protected function unEscapedToken(State $state, $buffer)
    {
        /** @var TextToken $token */
        $token = $state->createToken(TextToken::class);
        $token->setValue(preg_replace('/\\\\([#!]\\[|#\\{)/', '$1', $buffer));

        return $token;
    }

    protected function getUnescapedLines(State $state, $lines)
    {
        $buffer = '';
        $interpolationLevel = 0;
        foreach ($lines as $number => $lineValues) {
            if ($number) {
                $buffer .= "\n";
            }
            foreach ($lineValues as $value) {
                if (is_string($value)) {
                    if ($interpolationLevel) {
                        yield $this->unEscapedToken($state, $value);

                        continue;
                    }
                    $buffer .= $value;

                    continue;
                }

                if (!$interpolationLevel) {
                    yield $this->unEscapedToken($state, $buffer);

                    $buffer = '';
                }

                yield $value;

                if ($value instanceof TagInterpolationStartToken || $value instanceof InterpolationStartToken) {
                    $interpolationLevel++;
                }

                if ($value instanceof TagInterpolationEndToken || $value instanceof InterpolationEndToken) {
                    $interpolationLevel--;
                }
            }
        }

        //TODO: $state->endToken
        yield $this->unEscapedToken($state, $buffer);
    }

    public function scan(State $state)
    {
        $reader = $state->getReader();

        foreach ($state->scan(TextScanner::class) as $token) {
            yield $token;
        }

        if ($reader->peekNewLine()) {
            yield $state->createToken(NewLineToken::class);

            $reader->consume(1);

            $lines = [];
            $level = $state->getLevel();
            $newLevel = $level;
            $maxIndent = INF;

            while ($reader->hasLength()) {
                $indentationScanner = new IndentationScanner();
                $newLevel = $indentationScanner->getIndentLevel($state, $level);

                if (!$reader->peekChars([' ', "\t", "\n"])) {
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
                $indent = $reader->match('[ \t]+(?=\S)') ? mb_strlen($reader->getMatch(0)) : INF;
                if ($indent < $maxIndent) {
                    $maxIndent = $indent;
                }

                foreach ($state->scan(InterpolationScanner::class) as $subToken) {
                    $line[] = $subToken instanceof TextToken ? $subToken->getValue() : $subToken;
                }

                $text = $reader->readUntilNewLine();
                $line[] = $text;
                $lines[] = $line;

                if (!$reader->peekNewLine()) {
                    break;
                }

                $reader->consume(1);
            }

            if (count($lines)) {
                yield $state->createToken(IndentToken::class);

                if ($maxIndent > 0 && $maxIndent < INF) {
                    foreach ($lines as &$line) {
                        if (count($line) && is_string($line[0])) {
                            $line[0] = mb_substr($line[0], $maxIndent) ?: '';
                        }
                    }
                }

                foreach ($this->getUnescapedLines($state, $lines) as $token) {
                    yield $token;
                }

                if ($reader->hasLength()) {
                    yield $state->createToken(NewLineToken::class);

                    $state->setLevel($newLevel)->indent($level + 1);

                    while ($state->nextOutdent() !== false) {
                        yield $state->createToken(OutdentToken::class);
                    }
                }
            }
        }
    }
}
