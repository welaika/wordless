<?php

namespace Phug\Lexer\Event;

use Phug\Event;
use Phug\LexerEvent;

class EndLexEvent extends Event
{
    private $lexEvent;

    /**
     * EndLexEvent constructor.
     *
     * @param LexEvent $lexEvent
     */
    public function __construct(LexEvent $lexEvent)
    {
        parent::__construct(LexerEvent::END_LEX);

        $this->lexEvent = $lexEvent;
    }

    /**
     * @return LexEvent
     */
    public function getLexEvent()
    {
        return $this->lexEvent;
    }
}
