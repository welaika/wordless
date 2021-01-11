<?php

namespace Phug;

use Phug\Util\Exception\LocatedException;

/**
 * An exception thrown by the pug reader.
 */
class ReaderException extends LocatedException
{
    public static function message($message, array $details = [])
    {
        return static::getFailureMessage('read', $message, $details);
    }
}
