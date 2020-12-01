<?php

/**
 * Scanner for multiline texts (or some similar contents).
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Analyzer\LineAnalyzer;
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

    protected function getUnescapedLineValue(State $state, $value, &$interpolationLevel, &$buffer)
    {
        if (is_string($value)) {
            if ($interpolationLevel) {
                yield $this->unEscapedToken($state, $value);

                return;
            }

            $buffer .= $value;

            return;
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

    protected function getUnescapedLines(State $state, $lines)
    {
        $buffer = '';
        $interpolationLevel = 0;

        foreach ($lines as $number => $lineValues) {
            if ($number) {
                $buffer .= "\n";
            }

            foreach ($lineValues as $value) {
                foreach ($this->getUnescapedLineValue($state, $value, $interpolationLevel, $buffer) as $token) {
                    yield $token;
                }
            }
        }

        //TODO: $state->endToken
        yield $this->unEscapedToken($state, $buffer);
    }

    private function yieldLines(State $state, array $lines, LineAnalyzer $analyzer)
    {
        $reader = $state->getReader();

        yield $state->createToken(IndentToken::class);

        $maxIndent = $analyzer->getMaxIndent();

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

            $state->setLevel($analyzer->getNewLevel())->indent($analyzer->getLevel() + 1);

            while ($state->nextOutdent() !== false) {
                yield $state->createToken(OutdentToken::class);
            }
        }
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

            $analyzer = new LineAnalyzer($state, $reader);
            $analyzer->analyze(true);

            if (count($lines = $analyzer->getLines())) {
                foreach ($this->yieldLines($state, $lines, $analyzer) as $token) {
                    yield $token;
                }
            }
        }
    }
}
