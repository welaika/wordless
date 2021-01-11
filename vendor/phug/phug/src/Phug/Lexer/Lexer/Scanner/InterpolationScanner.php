<?php

/**
 * @example p Text #{'interpolation'} text
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TextToken;

class InterpolationScanner implements ScannerInterface
{
    protected $interpolationChars = [
        'tagInterpolation' => ['[', ']'],
        'interpolation'    => ['{', '}'],
    ];

    protected $regExp;

    public function __construct()
    {
        $interpolations = [];
        $backIndex = 2;

        foreach ($this->interpolationChars as $name => list($start, $end)) {
            $start = preg_quote($start, '/');
            $end = preg_quote($end, '/');
            $interpolations[] = $start.'(?<'.$name.'>'.
                '(?>"(?:\\\\[\\S\\s]|[^"\\\\])*"|\'(?:\\\\[\\S\\s]|[^\'\\\\])*\'|[^'.
                $start.$end.
                '\'"]++|(?-'.$backIndex.'))*+'.
                ')'.$end;
            $backIndex++;
        }

        $this->regExp = '(?<text>.*?)'.
            '(?<!\\\\)'.
            '(?<escape>#|!(?='.preg_quote($this->interpolationChars['interpolation'][0], '/').'))'.
            '(?<wrap>'.implode('|', $interpolations).')';
    }

    protected function throwEndOfLineExceptionIf(State $state, $condition)
    {
        if ($condition) {
            $state->throwException('End of line was reached with no closing bracket for interpolation.');
        }
    }

    protected function scanTagInterpolation(State $state, $tagInterpolation)
    {
        /** @var TagInterpolationStartToken $start */
        $start = $state->createToken(TagInterpolationStartToken::class);
        /** @var TagInterpolationEndToken $end */
        $end = $state->createToken(TagInterpolationEndToken::class);

        $start->setEnd($end);
        $end->setStart($start);

        $lexer = $state->getLexer();

        yield $start;

        foreach ($lexer->lex($tagInterpolation) as $token) {
            $this->throwEndOfLineExceptionIf($state, $token instanceof NewLineToken);

            yield $token;
        }

        yield $end;
    }

    protected function scanExpressionInterpolation(State $state, $interpolation, $escape)
    {
        /** @var InterpolationStartToken $start */
        $start = $state->createToken(InterpolationStartToken::class);
        /** @var InterpolationEndToken $end */
        $end = $state->createToken(InterpolationEndToken::class);

        $start->setEnd($end);
        $end->setStart($start);

        /** @var ExpressionToken $token */
        $token = $state->createToken(ExpressionToken::class);
        $token->setValue($interpolation);

        if ($escape === '#') {
            $token->escape();
        }

        yield $start;
        yield $token;
        yield $end;
    }

    protected function scanInterpolation(State $state, $tagInterpolation, $interpolation, $escape)
    {
        $this->throwEndOfLineExceptionIf(
            $state,
            !$state->getOption('multiline_interpolation') && strpos($interpolation, "\n") !== false
        );

        if ($tagInterpolation) {
            return $this->scanTagInterpolation($state, $tagInterpolation);
        }

        return $this->scanExpressionInterpolation($state, $interpolation, $escape);
    }

    protected function needSeparationBlankLine(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->peekNewLine()) {
            return false;
        }

        $indentWidth = $state->getIndentWidth();
        $indentation = $indentWidth > 0 ? $state->getIndentStyle().'{'.$state->getIndentWidth().'}' : '';

        return $reader->match('\n*'.$indentation.'\|');
    }

    public function scan(State $state)
    {
        $reader = $state->getReader();

        while ($reader->match($this->regExp)) {
            $text = $reader->getMatch('text');
            $text = preg_replace('/\\\\([#!]\\[|#{)/', '$1', $text);

            if (mb_strlen($text) > 0) {
                /** @var TextToken $token */
                $token = $state->createToken(TextToken::class);
                $token->setValue($text);

                yield $token;
            }

            foreach ($this->scanInterpolation(
                $state,
                $reader->getMatch('tagInterpolation'),
                $reader->getMatch('interpolation'),
                $reader->getMatch('escape')
            ) as $token) {
                yield $token;
            }

            $reader->consume();

            if ($this->needSeparationBlankLine($state)) {
                /** @var TextToken $token */
                $token = $state->createToken(TextToken::class);
                $token->setValue("\n");

                yield $token;
            }
        }
    }
}
