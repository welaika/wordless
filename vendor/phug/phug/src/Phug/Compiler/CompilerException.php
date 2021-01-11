<?php

namespace Phug;

use Phug\Util\Exception\LocatedException;

/**
 * Represents an exception that is thrown during the compiling process.
 */
class CompilerException extends LocatedException
{
    public static function message($message, array $details = [])
    {
        return static::getFailureMessage('compile', $message, $details);
    }
}
