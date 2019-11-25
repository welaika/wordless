<?php

namespace Phug\Util\Partial;

/**
 * Class SubjectTrait.
 */
trait SubjectTrait
{
    /**
     * @var string
     */
    private $subject = null;

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }
}
