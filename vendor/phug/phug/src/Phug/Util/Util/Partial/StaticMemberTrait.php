<?php

namespace Phug\Util\Partial;

use Phug\Util\PhpTokenizer;

trait StaticMemberTrait
{
    /**
     * @param string $member optional member to check.
     *
     * @return bool
     */
    public function hasStaticMember($member)
    {
        if (is_string($this->$member)) {
            $tokens = PhpTokenizer::getTokens($this->$member);

            return
                count($tokens) === 1 &&
                is_array($tokens[0]) &&
                in_array($tokens[0][0], [T_CONSTANT_ENCAPSED_STRING, T_DNUMBER, T_LNUMBER]);
        }

        return false;
    }
}
