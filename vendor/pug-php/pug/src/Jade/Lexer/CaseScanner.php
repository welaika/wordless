<?php

namespace Jade\Lexer;

/**
 * Class Jade\Lexer\CaseScanner.
 */
abstract class CaseScanner extends BlockScanner
{
    /**
     * @return object
     */
    protected function scanCase()
    {
        return $this->scan('/^case +([^\n]+)/', 'case');
    }

    /**
     * @return object
     */
    protected function scanWhen()
    {
        return $this->scan('/^when +((::|[^\n:]+)+)/', 'when');
    }

    /**
     * @return object
     */
    protected function scanDefault()
    {
        return $this->scan('/^default */', 'default');
    }
}
