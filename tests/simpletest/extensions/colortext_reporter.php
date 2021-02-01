<?php

require_once __DIR__ . '/../reporter.php';

/**
 * Provides an ANSI-colored {@link TextReporter} for viewing test results.
 *
 * @author Jason Sweat (original code)
 * @author Travis Swicegood <development@domain51.com>
 */
class ColorTextReporter extends TextReporter
{
    public $_failColor = 41;
    public $_passColor = 42;

    /**
     * Handle initialization
     *
     * @param {@link TextReporter}
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Capture the attempt to display the final test results
     * and insert the ANSI-color codes in place.
     *
     * @param string
     *
     * @see TextReporter
     */
    public function paintFooter($test_name)
    {
        ob_start();
        parent::paintFooter($test_name);
        $output = trim(ob_get_clean());
        if ($output) {
            if (($this->getFailCount() + $this->getExceptionCount()) == 0) {
                $color = $this->_passColor;
            } else {
                $color = $this->_failColor;
            }

            $this->_setColor($color);
            echo $output;
            $this->_resetColor();
        }
    }

    /**
     * Sets the terminal to an ANSI-standard $color
     *
     * @param int
     */
    public function _setColor($color)
    {
        printf("%s[%sm\n", chr(27), $color);
    }

    /**
     * Resets the color back to normal.
     */
    public function _resetColor()
    {
        $this->_setColor(0);
    }
}
