<?php

/**
 * @example <span>Raw html</span>
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Analyzer\LineAnalyzer;
use Phug\Lexer\State;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TextToken;

class MarkupScanner extends MultilineScanner
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->peekChar('<')) {
            return;
        }

        if ($state->getOption('multiline_markup_enabled')) {
            $analyzer = new LineAnalyzer($state, $reader);
            $analyzer->analyze(false, ['<']);
            $lines = $analyzer->getLines();

            foreach ($this->getUnescapedLines($state, $lines) as $token) {
                yield $token;
            }

            if ($analyzer->hasNewLine()) {
                yield $state->createToken(NewLineToken::class);

                if ($analyzer->hasOutdent()) {
                    yield $state->createToken(OutdentToken::class);
                }
            }

            return;
        }

        /** @var TextToken $text */
        $text = $state->createToken(TextToken::class);
        $text->setValue($reader->readUntilNewLine());

        yield $text;
    }
}
