<?php

namespace Phug\Util;

class PhpTokenizer
{
    public static function getTokens($code)
    {
        return array_slice(token_get_all('<?php '.$code), 1);
    }
}
