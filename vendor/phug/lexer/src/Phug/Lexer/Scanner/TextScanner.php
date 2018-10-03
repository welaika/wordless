<?php

/**
 * @example | Text
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TextToken;
use Phug\Lexer\TokenInterface;

class TextScanner implements ScannerInterface
{
    const INTERPOLATION_ENABLED = true;

    private function leftTrimValueIfNotAfterInterpolation(State $state, TextToken $token)
    {
        $text = $token->getValue();
        if (in_array(mb_substr($text, 0, 1), [' ', "\t"])) {
            $previous = $state->getLastToken();
            if (!(
                $previous instanceof TagInterpolationEndToken ||
                $previous instanceof InterpolationEndToken
            )) {
                $token->setValue(mb_substr($text, 1) ?: '');
            }
        }
    }

    private function scanInterpolationToken(State $state, TokenInterface $subToken)
    {
        if ($subToken instanceof InterpolationStartToken ||
            $subToken instanceof TagInterpolationStartToken
        ) {
            /** @var TextToken $token */
            $token = $state->createToken(TextToken::class);
            $token->setValue('');

            yield $token;
        }
        if ($subToken instanceof TextToken) {
            $this->leftTrimValueIfNotAfterInterpolation($state, $subToken);
        }
    }

    private function scanInterpolationTokens(State $state, &$firstToken)
    {
        if (static::INTERPOLATION_ENABLED) {
            foreach ($state->scan(InterpolationScanner::class) as $subToken) {
                if ($firstToken) {
                    $firstToken = false;
                    foreach ($this->scanInterpolationToken($state, $subToken) as $token) {
                        yield $token;
                    }
                }

                yield $subToken;
            }
        }
    }

    public function scan(State $state)
    {
        $reader = $state->getReader();
        $firstToken = true;

        foreach ($this->scanInterpolationTokens($state, $firstToken) as $token) {
            yield $token;
        }

        /** @var TextToken $token */
        $token = $state->createToken(TextToken::class);
        $text = $reader->readUntilNewLine();

        if (mb_strlen($text) < 1) {
            return;
        }

        //Always omit the very first space in basically every text (if there is one)
        if ($firstToken && in_array(mb_substr($text, 0, 1), [' ', "\t"])) {
            $previous = $state->getLastToken();
            if (!(
                $previous instanceof TagInterpolationEndToken ||
                $previous instanceof InterpolationEndToken
            )) {
                $text = mb_substr($text, 1);
            }
        }

        $text = preg_replace('/\\\\([#!]\\[|#\\{)/', '$1', $text);
        $token->setValue($text);

        yield $state->endToken($token);
    }
}
