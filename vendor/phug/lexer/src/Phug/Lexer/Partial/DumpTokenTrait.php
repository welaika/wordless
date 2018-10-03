<?php

namespace Phug\Lexer\Partial;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TextToken;
use Phug\Lexer\TokenInterface;

trait DumpTokenTrait
{
    private function getTokenName($token)
    {
        return preg_replace('/Token$/', '', get_class($token));
    }

    private function dumpAttributeToken(AttributeToken $token)
    {
        return sprintf(
            'Attr %s=%s (%s, %s)',
            $token->getName() ?: '""',
            $token->getValue() ?: '""',
            $token->isEscaped() ? 'escaped' : 'unescaped',
            $token->isChecked() ? 'checked' : 'unchecked'
        );
    }

    private function dumpTextToken(TextToken $token)
    {
        return 'Text '.$token->getValue();
    }

    private function dumpExpressionToken(ExpressionToken $token)
    {
        return sprintf(
            'Expr %s (%s, %s)',
            $token->getValue() ?: '""',
            $token->isEscaped() ? 'escaped' : 'unescaped',
            $token->isChecked() ? 'checked' : 'unchecked'
        );
    }

    private function getTokenSymbol(TokenInterface $token)
    {
        static $symbols = [
            IndentToken::class         => '->',
            OutdentToken::class        => '<-',
            NewLineToken::class        => '\n',
            AttributeStartToken::class => '(',
            AttributeToken::class      => 'dumpAttributeToken',
            AttributeEndToken::class   => ')',
            TextToken::class           => 'dumpTextToken',
            ExpressionToken::class     => 'dumpExpressionToken',
        ];

        $className = get_class($token);

        if (isset($symbols[$className])) {
            return substr($symbols[$className], 0, 4) === 'dump' ?
                call_user_func([$this, $symbols[$className]], $token) :
                $symbols[$className];
        }

        return $this->getTokenName($token);
    }

    private function dumpToken(TokenInterface $token)
    {
        $dumped = $this->getTokenSymbol($token);

        return "[$dumped]".($dumped === '\n' ? "\n" : '');
    }
}
