<?php

/**
 * @example (foo="bar")
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Reader;

class AttributeScanner implements ScannerInterface
{
    private function skipComments(Reader $reader)
    {
        $reader->readSpaces();
        if ($reader->peekString('//')) {
            $reader->consume();
            $reader->readUntilNewLine();
        }
        $reader->readSpaces();
    }

    private function isTruncatedValue($expression)
    {
        $expression = preg_replace('/
                "(?:\\\\[\\S\\s]|[^"\\\\])*"|
                \'(?:\\\\[\\S\\s]|[^\'\\\\])*\'
            /x', '0', $expression);
        $expression = preg_replace('/\\s*(
                (\\[([^\\[\\]]+|(?1))*\\]) |
                (\\(([^\\(\\)]+|(?1))*\\)) |
                (\\{([^\\{\\}]+|(?1))*\\})
            )/x', '0', $expression);

        return preg_match('/\?.*:\s*$/', $expression);
    }

    private function isTruncatedExpression(Reader $reader, &$expression)
    {
        if (mb_substr($expression, -3) === 'new' || mb_substr($expression, -5) === 'clone') {
            $expression .= $reader->getLastPeekResult();
            $reader->consume();

            return true;
        }

        if ($reader->match('[\\t ]*((<|>|==|!=|\\+|-|\\*|\\/|%)=?)[\\t ]*') ||
            $reader->match('[\\t ]*[+\\/*%-]') ||
            $reader->match('[\\t ]*(\\?'.
                '(?:(?>"(?:\\\\[\\S\\s]|[^"\\\\])*"|\'(?:\\\\[\\S\\s]|[^\'\\\\])*\'|[^\\?\\:\'"]++|(?-1))*+)'.
            '\\:)')
        ) {
            $expression .= $reader->getMatch(0);
            $reader->consume();
            $expression .= $reader->readSpaces();

            return !$reader->peekChar(')');
        }

        return false;
    }

    private function readAssignOperator(Reader $reader, AttributeToken $token)
    {
        return ($reader->peekString('?!=') && $token->unescape() && $token->uncheck() ||
            $reader->peekString('?=') && $token->uncheck() ||
            $reader->peekString('!=') && $token->unescape() ||
            $reader->peekChar('=')
        ) && $reader->consume();
    }

    private function isExpressionPartial(Reader $reader, $expression)
    {
        return (
            $reader->match('\\s*(
                "(?:\\\\[\\S\\s]|[^"\\\\])*" |
                \'(?:\\\\[\\S\\s]|[^\'\\\\])*\' |
                (\\[([^\\[\\]\'"]+|(?1))*\\]) |
                (\\(([^\\(\\)\'"]+|(?1))*\\)) |
                (\\{([^\\{\\}\'"]+|(?1))*\\})
            )', 'x') &&
            !preg_match('/[\'"]$/', $expression)
        ) ||
        $reader->match('\\s*\?((
            \s+ |
            "(?:\\\\[\\S\\s]|[^"\\\\])*" |
            \'(?:\\\\[\\S\\s]|[^\'\\\\])*\' |
            (\\[([^\\[\\]\'"]+|(?1))*\\]) |
            (\\(([^\\(\\)\'"]+|(?1))*\\)) |
            (\\{([^\\{\\}\'"]+|(?1))*\\})
        )+)\\:', 'x') ||
        $reader->match('\s+(\?[?:]|[.%*^&|!~[{+-]|\/(?!\/))') || (
            $reader->match('\s') &&
            preg_match('/(\?[?:]|[.%*^&|!~\/}+-])\s*$/', $expression)
        ) ||
        $this->isTruncatedValue($expression);
    }

    private function getAttributeValue(Reader $reader, array $chars = null)
    {
        $chars = $chars ?: [
            ' ', "\t", "\n", ',', ')', '//',
        ];
        $joinChars = array_merge($chars, ['"', "'"]);
        $expression = $reader->readExpression($chars);
        while ($this->isExpressionPartial($reader, $expression)) {
            $match = $reader->getMatch(0);
            $expression .= $match;
            $reader->consume(mb_strlen($match));
            $expression .= $reader->readExpression($joinChars);
        }

        return $expression;
    }

    private function readAttributeValue(Reader $reader, AttributeToken $token)
    {
        $expression = $this->getAttributeValue($reader);
        while ($this->isTruncatedExpression($reader, $expression)) {
            $this->skipComments($reader);
            $expression .= $this->getAttributeValue($reader);
        }
        $token->setValue($expression);

        //Ignore a comma if found
        if ($reader->peekChar(',')) {
            $reader->consume();
        }

        //And check for comments again
        // a(
        //  href='value' //<- Awesome attribute, i say
        //  )
        $this->skipComments($reader);
    }

    private function readExpression(Reader $reader)
    {
        //Read the first part of the expression
        //e.g.:
        // (`a`), (`a`=b), (`$expression1`, `$expression2`) (`$expression` `$expression`=a)
        $expression = $this->getAttributeValue($reader, [
            ' ', "\t", "\n", ',', '?!=', '?=', '!=', '=', ')', '//',
        ]);

        //Notice we have the following problem with spaces:
        //1. You can separate arguments with spaces
        // -> a(a=a b=b c=c)
        //2. You can have spaces around anything
        // -> a(a =
        //       a c=c d
        // =  d)
        //3. You can also separate with tabs and line-breaks
        // -> a(
        //      a=a
        //      b=b
        //      c=c
        //    )
        //
        //This leads to commas actually being just ignored as the most
        //simple solution. Attribute finding passes on as long as there's
        //no ) or EOF in sight.
        //TODO: Afaik this could also lead to a(a=(b ? b : c)d=f), where a space or
        // anything else _should_ be required.
        // Check this.

        //Ignore the comma. It's mainly just a "visual" separator,
        //it's actually completely optional.
        if ($reader->peekChar(',')) {
            $reader->consume();
        }

        if ($expression === null || $expression === '') {
            //An empty attribute would mean we did something like
            //,, or had a space before a comma (since space is also a valid
            //separator
            //We just skip that one.
            if ($reader->peekChar('=') ||
                $reader->peekString('?!=') ||
                $reader->peekString('?=') ||
                $reader->peekString('!=')
            ) {
                $reader->consume();
            }

            $expression = null;
        }

        return $expression;
    }

    private function getAttributeToken(State $state)
    {
        $reader = $state->getReader();

        //Check for comments
        // a( //Now attributes follow!
        //   a=a...
        $this->skipComments($reader);

        //We create the attribute token first (we don't need to yield it
        //but we fill it sequentially)
        /** @var AttributeToken $token */
        $token = $state->createToken(AttributeToken::class);
        $token->escape();
        $token->check();

        if ($variadic = $reader->peekString('...')) {
            $token->setIsVariadic(true);
            $reader->consume();
        }

        return $token;
    }

    private function seedAttributeToken(State $state, AttributeToken $token, $expression)
    {
        $reader = $state->getReader();

        $token->setName($expression);

        //Check for comments at this point
        // a(
        //      href //<- The name of the thing
        //      = 'value' //<- The value of the thing
        $this->skipComments($reader);

        //Check for our assignment-operators.
        //Notice that they have to be exactly written in the correct order
        //? first, ! second, = last (and required!)
        //It's made like this on purpose so that the Jade code is consistent
        //later on. It also makes this part of the lexing process easier and
        //more reliable.
        //If any of the following assignment operators have been found,
        //we REQUIRE a following expression as the attribute value
        $hasValue = $this->readAssignOperator($reader, $token);

        //Check for comments again
        // a(
        //  href= //Here be value
        //      'value'
        //  )
        $this->skipComments($reader);

        if ($hasValue) {
            $this->readAttributeValue($reader, $token);
        }
    }

    private function scanParenthesesContent(State $state)
    {
        $reader = $state->getReader();

        while ($reader->hasLength()) {
            $token = $this->getAttributeToken($state);

            if (($expression = $this->readExpression($reader)) === null) {
                continue;
            }

            $this->seedAttributeToken($state, $token, $expression);

            yield $token;

            if (!$reader->peekChar(')')) {
                continue;
            }

            break;
        }
    }

    private function scanParentheses(State $state)
    {
        $reader = $state->getReader();

        if ($reader->peekChar(')')) {
            return;
        }

        foreach ($this->scanParenthesesContent($state) as $token) {
            yield $token;
        }

        if (!$reader->peekChar(')')) {
            $state->throwException(
                'Unclosed attribute block'
            );
        }
    }

    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->peekChar('(')) {
            return;
        }

        $start = $state->createToken(AttributeStartToken::class);

        $reader->consume();
        yield $state->endToken($start);

        foreach ($this->scanParentheses($state) as $token) {
            yield $token;
        }

        $end = $state->createToken(AttributeEndToken::class);
        $reader->consume();
        yield $state->endToken($end);

        foreach ($state->scan(ElementScanner::class) as $subToken) {
            yield $subToken;
        }
    }
}
