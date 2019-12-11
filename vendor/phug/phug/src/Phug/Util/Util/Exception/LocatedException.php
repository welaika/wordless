<?php

namespace Phug\Util\Exception;

use Exception;
use Phug\Util\SourceLocationInterface;

class LocatedException extends Exception
{
    private $location;

    public function __construct(
        SourceLocationInterface $location,
        $message = '',
        $code = 0,
        $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->location = $location;
    }

    /**
     * @return SourceLocationInterface
     */
    public function getLocation()
    {
        return $this->location;
    }

    public static function getFailureMessage($action, $message, array $details = [])
    {
        $message = "Failed to $action: $message";

        foreach ($details as $name => $value) {
            if ($value || $value === 0) {
                $message .= "\n".ucfirst($name).': '.$value;
            }
        }

        return $message;
    }
}
