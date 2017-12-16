<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\FilterToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TextToken;

class FilterScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        foreach ($state->scanToken(
            FilterToken::class,
            ':(?<name>[a-zA-Z_][a-zA-Z0-9\-_]*(?::[a-zA-Z_][a-zA-Z0-9\-_]*)*)(?=\s|\()'
        ) as $token) {
            yield $token;

            foreach ($state->scan(AttributeScanner::class) as $subToken) {
                yield $subToken;
            }

            if ($reader->match('[\t ]')) {
                $reader->consume(1);
                /** @var TextToken $token */
                $token = $state->createToken(TextToken::class);
                $token->setValue($reader->readUntilNewLine());

                yield $state->endToken($token);

                continue;
            }

            $level = $state->getLevel();
            $newLevel = $level;
            $lines = [];
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
                        $lines[] = '';

                        continue;
                    }

                    $state->setLevel($newLevel);

                    break;
                }

                $line = $reader->readUntilNewLine();
                $lines[] = $line;
                $unIndentedLine = ltrim($line);
                if ($unIndentedLine !== '') {
                    $indent = mb_strlen($line) - mb_strlen($unIndentedLine);
                    if ($indent < $maxIndent) {
                        $maxIndent = $indent;
                    }
                }

                if (!$reader->peekNewLine()) {
                    break;
                }

                $reader->consume(1);
            }

            yield $state->createToken(NewLineToken::class);
            yield $state->createToken(IndentToken::class);

            /** @var TextToken $token */
            $token = $state->createToken(TextToken::class);
            if ($maxIndent > 0 && $maxIndent < INF) {
                foreach ($lines as &$line) {
                    $line = mb_substr($line, $maxIndent) ?: '';
                }
            }
            $token->setValue(implode("\n", $lines));

            //TODO: As it seems, this is the only TextToken that will actually contain newlines, thus Stat->endToken
            // will end up with a wrong line offset. This is why endToken is not applied at all here and only the
            // start position will be kept
            $token->getSourceLocation()->setOffsetLength(1); //Let it have at least 1 length for debugging
            yield $token;

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
