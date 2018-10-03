<?php

/**
 * @example <span>Raw html</span>
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Analyzer\LineAnalyzer;
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

        if (!$state->getOption('multiline_markup_enabled')) {
            /** @var TextToken $token */
            $token = $state->createToken(TextToken::class);
            $token->setValue($reader->readUntilNewLine());

            yield $token;

            return;
        }

        $analyzer = new LineAnalyzer($state, $reader);
        $analyzer->analyze(false, ['<']);
        $lines = $analyzer->getLines();

        foreach ($this->getUnescapedLines($state, $lines) as $token) {
            yield $token;
        }

        if ($analyzer->hasNewLine()) {
            yield $state->createToken(NewLineToken::class);
        }
    }
}
