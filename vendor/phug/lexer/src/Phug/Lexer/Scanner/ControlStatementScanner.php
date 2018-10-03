<?php

/**
 * Abstract class for while, for, do, when, case, if.
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Scanner\Partial\NamespaceAndTernaryTrait;
use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;

abstract class ControlStatementScanner implements ScannerInterface
{
    use NamespaceAndTernaryTrait;

    private $tokenClassName;
    private $names;

    public function __construct($tokenClassName, array $names)
    {
        $this->tokenClassName = $tokenClassName;
        $this->names = $names;
    }

    public function scan(State $state)
    {
        $reader = $state->getReader();
        $names = implode('|', $this->names);

        if (!$reader->match("({$names})[ \t\n:]", null, " \t\n:") &&
            !$reader->match("({$names})(?=\()", null, " \t\n:")) {
            return;
        }

        $token = $state->createToken($this->tokenClassName);
        $name = $reader->getMatch(1);
        $reader->consume();

        //Ignore spaces after identifier
        $reader->readIndentation();

        if (method_exists($token, 'setName')) {
            $token->setName($name);
        }

        if (method_exists($token, 'setSubject')) {
            $subject = $this->checkForNamespaceAndTernary($reader);

            $token->setSubject($subject !== '' ? $subject : null);
        }

        yield $state->endToken($token);

        foreach ($state->scan(SubScanner::class) as $token) {
            yield $token;
        }
    }
}
