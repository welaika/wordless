<?php

namespace Phug\Lexer\Event;

use InvalidArgumentException;
use Phug\Event;
use Phug\Lexer\TokenInterface;
use Phug\LexerEvent;
use Phug\Util\Collection;
use Traversable;

class TokenEvent extends Event
{
    /**
     * Token if only one token to handle.
     *
     * @var TokenInterface
     */
    private $token;

    /**
     * Token traversable object if multiple tokens to handle.
     *
     * @var Traversable|array|null
     */
    private $tokenGenerator = null;

    public function __construct(TokenInterface $token)
    {
        parent::__construct(LexerEvent::TOKEN);

        $this->token = $token;
    }

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }

    /**
     * @return iterable|null
     */
    public function getTokenGenerator()
    {
        return $this->tokenGenerator;
    }

    /**
     * @param iterable|null $tokens
     */
    public function setTokenGenerator($tokens)
    {
        if ($tokens !== null && !Collection::isIterable($tokens)) {
            throw new InvalidArgumentException(
                'setTokenGenerator(iterable $tokens) expect its argument to be iterable, '.
                (is_object($tokens) ? get_class($tokens) : gettype($tokens)).' received.'
            );
        }

        $this->tokenGenerator = $tokens;
    }
}
