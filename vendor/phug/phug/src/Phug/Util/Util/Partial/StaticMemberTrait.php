<?php

namespace Phug\Util\Partial;

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
            $tokens = token_get_all('<?php '.$this->$member);

            return
                count($tokens) === 2 &&
                is_array($tokens[1]) &&
                in_array($tokens[1][0], [T_CONSTANT_ENCAPSED_STRING, T_DNUMBER, T_LNUMBER]);
        }

        return false;
    }
}
