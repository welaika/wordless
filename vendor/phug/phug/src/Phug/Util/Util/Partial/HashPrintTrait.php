<?php

namespace Phug\Util\Partial;

use Phug\Util\Hasher;

/**
 * Class HashPrintTrait.
 *
 * Provides a protected method hashPrint to get an hashed blueprint for an input file or content.
 */
trait HashPrintTrait
{
    /**
     * Return a hashed print from input file or content.
     *
     * @param string $input
     *
     * @return string
     */
    protected function hashPrint($input)
    {
        $hasher = new Hasher($input);

        return $hasher->hash();
    }
}
