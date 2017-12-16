<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer;
use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Reader;

class IndentationScanner implements ScannerInterface
{
    protected function getLevelFromIndent(State $state, $indent)
    {
        return mb_strlen(str_replace(
            Lexer::INDENT_TAB,
            str_repeat(Lexer::INDENT_SPACE, Lexer::DEFAULT_TAB_WIDTH),
            $indent
        ));
    }

    protected function getIndentChar(Reader $reader)
    {
        $char = null;

        if ($reader->peekIndentation()) {
            $char = $reader->getLastPeekResult();
            $reader->consume();
        }

        return $char;
    }

    protected function formatIndentChar(State $state, $indentChar)
    {
        $isTab = $indentChar === Lexer::INDENT_TAB;
        $indentStyle = $isTab ? Lexer::INDENT_TAB : Lexer::INDENT_SPACE;
        //Update the indentation style
        if (!$state->getIndentStyle()) {
            $state->setIndentStyle($indentStyle);
        }
        if ($state->getIndentStyle() !== $indentStyle && !$state->getOption('allow_mixed_indent')) {
            $state->throwException(
                'Invalid indentation, you can use tabs or spaces but not both'
            );
        }

        return $indentChar;
    }

    public function getIndentLevel(State $state, $maxLevel = INF, callable $getIndentChar = null)
    {
        if ($maxLevel <= 0) {
            return 0;
        }

        $reader = $state->getReader();
        $indent = '';

        if (is_null($getIndentChar)) {
            $getIndentChar = [$this, 'getIndentChar'];
        }

        while ($indentChar = call_user_func($getIndentChar, $reader)) {
            $indent .= $this->formatIndentChar($state, $indentChar);
            if ($state->getIndentWidth() &&
                $this->getLevelFromIndent($state, $indent) >= $maxLevel
            ) {
                break;
            }
        }

        if (!$state->getIndentWidth() &&
            mb_strpos($indent, Lexer::INDENT_SPACE) !== false &&
            mb_strpos($indent, Lexer::INDENT_TAB) !== false
        ) {
            $state->setIndentWidth(Lexer::DEFAULT_TAB_WIDTH);
        }

        //Update the indentation width
        $length = $this->getLevelFromIndent($state, $indent);
        if ($length && !$state->getIndentWidth()) {
            //We will use the pretty first indentation as our indent width
            $state->setIndentWidth($length);
        }

        return $length;
    }

    protected function setStateLevel(State $state, $indent)
    {
        $oldLevel = $state->getLevel();
        $newLevel = $this->getIndentLevel($state, INF, function () use (&$indent) {
            $char = null;

            if (mb_strlen($indent)) {
                $char = mb_substr($indent, 0, 1);
                $indent = mb_substr($indent, 1);
            }

            return $char;
        });

        $state->setLevel($newLevel);

        return $state->getLevel() - $oldLevel;
    }

    public function scan(State $state)
    {
        $reader = $state->getReader();

        //TODO: $state->endToken
        //There's no indentation if we're not at the start of a line
        if ($reader->getOffset() !== 1) {
            return;
        }

        $indent = $reader->readIndentation();

        //If this is an empty line, we ignore the indentation completely.
        foreach ($state->scan(NewLineScanner::class) as $token) {
            yield $token;

            return;
        }

        //We create a token for each indentation/outdentation
        if ($this->setStateLevel($state, $indent) > 0) {
            $state->indent();

            yield $state->createToken(IndentToken::class);

            return;
        }

        while ($state->nextOutdent() !== false) {
            yield $state->createToken(OutdentToken::class);
        }
    }
}
