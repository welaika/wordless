<?php

namespace Phug\Lexer\Partial;

use Phug\Lexer\State;

trait StateTrait
{
    /**
     * The state of the current lexing process.
     *
     * @var State
     */
    private $state;

    /**
     * Returns true if a lexing process is active and a state exists, false if not.
     *
     * @return bool
     */
    public function hasState()
    {
        return $this->state instanceof State;
    }

    /**
     * Returns the state object of the current lexing process.
     *
     * @return State
     */
    public function getState()
    {
        if (!$this->state) {
            throw new \RuntimeException(
                'Failed to get state: No lexing process active. Use the `lex()`-method'
            );
        }

        return $this->state;
    }
}
