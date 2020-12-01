<?php

namespace Phug;

use Phug\Lexer\TokenInterface;
use Phug\Util\Exception\LocatedException;
use Phug\Util\SourceLocation;

/**
 * Represents an exception that is thrown during the parsing process.
 */
class ParserException extends LocatedException
{
    private $relatedToken;

    public function __construct(
        SourceLocation $location,
        $message = '',
        $code = 0,
        TokenInterface $relatedToken = null,
        $previous = null
    ) {
        parent::__construct($location, $message, $code, $previous);

        $this->relatedToken = $relatedToken;
    }

    /**
     * @return TokenInterface
     */
    public function getRelatedToken()
    {
        return $this->relatedToken;
    }

    public static function message($message, array $details = [])
    {
        return static::getFailureMessage('parse', $message, $details);
    }
}
