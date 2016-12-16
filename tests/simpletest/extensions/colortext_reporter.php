<?php
/**
 *  Base include file for SimpleTest
 *  @package        SimpleTest
 *  @subpackage     Extensions
 *  @version        $Id$
 */

/**
 * include base reporter
 */
require_once(dirname(__FILE__) . '/../reporter.php');


/**
 * Provides an ANSI-colored {@link TextReporter} for viewing test results.
 *
 * This code is made available under the same terms as SimpleTest.  It is based
 * off of code that Jason Sweat originally published on the SimpleTest mailing
 * list. 
 *
 * @author Jason Sweat (original code)
 * @author Travis Swicegood <development@domain51.com>
 * @package SimpleTest
 * @subpackage Extensions
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
     * Capture the attempt to display the final test results and insert the 
     * ANSI-color codes in place.
     *
     * @param string
     * @see TextReporter
     * @access public
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
     * @access protected
     */
    public function _setColor($color)
    {
        printf("%s[%sm\n", chr(27), $color);
    }
    
    
    /**
     * Resets the color back to normal.
     *
     * @access protected
     */
    public function _resetColor()
    {
        $this->_setColor(0);
    }
}
