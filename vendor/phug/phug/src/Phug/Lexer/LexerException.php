<?php

namespace Phug;

use Phug\Util\Exception\LocatedException;

/**
 * Represents an exception that is thrown during the lexical analysis.
 *
 * This exception is thrown when the lexer encounters invalid token relations
 */
class LexerException extends LocatedException
{
    public static function message($message, array $details = [])
    {
        return static::getFailureMessage('lex', $message, $details);
    }
}
