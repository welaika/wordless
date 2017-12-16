<?php

namespace Phug\Lexer\Scanner\Partial;

trait NamespaceAndTernaryTrait
{
    private function checkForTernary($reader)
    {
        if ($reader->peekChars('?:')) {
            $subject = $reader->getLastPeekResult().' ';
            $reader->consume();

            return $subject;
        }

        $subject = ' '.$reader->readExpression(["\n", ':']).' ';

        if ($reader->peekChar(':')) {
            $subject .= $reader->getLastPeekResult().' ';
            $reader->consume();
        }

        return $subject;
    }

    private function checkForNamespaceAndTernary($reader)
    {
        $subject = $reader->readExpression(["\n", '?', ':']);

        //Handle `if Foo::bar`
        if ($reader->peekString('::')) {
            $subject .= $reader->getLastPeekResult();
            $reader->consume();

            $subject .= $reader->readExpression(["\n", ':']);
        } elseif ($reader->peekChar('?')) {
            $subject .= ' '.$reader->getLastPeekResult();
            $reader->consume();

            //Ternary expression
            $subject .= $this->checkForTernary($reader);

            $subject .= $reader->readExpression(["\n", ':']);
        }

        return trim($subject);
    }
}
