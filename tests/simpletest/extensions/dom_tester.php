<?php

require_once __DIR__ . '/../web_tester.php';
require_once __DIR__ . '/dom_tester/css_selector.php';

/**
 * CssSelectorExpectation
 *
 * Create a CSS Selector expectactation
 *
 * @author     Perrick Penet <perrick@noparking.net>
 *
 * @param DomDocument $_dom
 * @param string      $_selector
 * @param array       $_value
 */
class CssSelectorExpectation extends SimpleExpectation
{
    public $dom;
    public $selector;
    public $value;

    /**
     * Sets the dom tree and the css selector to compare against
     *
     * @param mixed $dom          Dom tree to search into.
     * @param mixed $selector     Css selector to match element.
     * @param string $message     Customised message on failure.
     */
    public function __construct($dom, $selector, $message = '%s')
    {
        parent::__construct($message);
        $this->dom      = $dom;
        $this->selector = $selector;

        $css_selector = new CssSelector($this->dom);
        $this->value  = $css_selector->getTexts($this->selector);
    }

    /**
     * Tests the expectation. True if it matches the held value.
     *
     * @param mixed $compare        Comparison value.
     *
     * @return bool              True if correct.
     */
    public function test($compare)
    {
        return (($this->value == $compare) && ($compare == $this->value));
    }

    /**
     * Returns a human readable test message.
     *
     * @param mixed $compare      Comparison value.
     *
     * @return string             Description of success or failure.
     */
    public function testMessage($compare)
    {
        $dumper = $this->getDumper();
        if (is_array($compare)) {
            sort($compare);
        }
        if ($this->test($compare)) {
            return 'CSS selector expectation [' . $dumper->describeValue($this->value) . ']' .
                    ' using [' . $dumper->describeValue($this->selector) . ']';
        } else {
            return 'CSS selector expectation [' . $dumper->describeValue($this->value) . ']' .
                    ' using [' . $dumper->describeValue($this->selector) . ']' .
                    ' fails with [' .
                    $dumper->describeValue($compare) . '] ' .
                    $dumper->describeDifference($this->value, $compare);
        }
    }
}

/**
 * DomTestCase
 *
 * Extend Web test case with DOM related assertions, CSS selectors in particular.
 */
class DomTestCase extends WebTestCase
{
   /*
    * @param DomDocument $dom
    */
    public $dom;

    public function loadDom()
    {
        $this->dom                  = new DomDocument('1.0', 'utf-8');
        $this->dom->validateOnParse = true;
        $this->dom->loadHTML($this->browser->getContent());
    }

    public function getElementsBySelector($selector)
    {
        $this->loadDom();

        $css_selector = new CssSelectorExpectation($this->dom, $selector);

        return $css_selector->value;
    }

    public function assertElementsBySelector($selector, $elements, $message = '%s')
    {
        $this->loadDom();

        return $this->assert(new CssSelectorExpectation($this->dom, $selector), $elements, $message);
    }
}
