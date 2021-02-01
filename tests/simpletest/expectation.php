<?php

require_once __DIR__ . '/dumper.php';
require_once __DIR__ . '/compatibility.php';

/**
 * Assertion that can display failure information. Also includes various helper methods.
 *
 * @abstract
 */
class SimpleExpectation
{
    protected $dumper = false;
    private $message;

    /**
     * Creates a dumper for displaying values and sets the test message.
     *
     * @param string $message    Customised message on failure.
     */
    public function __construct($message = '%s')
    {
        $this->message = $message;
    }

    /**
     * Tests the expectation. True if correct.
     *
     * @param mixed $compare        Comparison value.
     *
     * @return bool              True if correct.
     *
     * @abstract
     */
    public function test($compare)
    {
    }

    /**
     * Returns a human readable test message.
     *
     * @param mixed $compare      Comparison value.
     *
     * @return string             Description of success or failure.
     *
     * @abstract
     */
    public function testMessage($compare)
    {
    }

    /**
     * Overlays the generated message onto the stored user message.
     * An additional message can be interjected.
     *
     * @param mixed $compare        Comparison value.
     * @param SimpleDumper $dumper  For formatting the results.
     *
     * @return string               Description of success or failure.
     */
    public function overlayMessage($compare, $dumper)
    {
        $this->dumper = $dumper;

        return sprintf($this->message, $this->testMessage($compare));
    }

    /**
     * Accessor for the dumper.
     *
     * @return SimpleDumper    Current value dumper.
     */
    protected function getDumper()
    {
        if (! $this->dumper) {
            $dumper = new SimpleDumper();

            return $dumper;
        }

        return $this->dumper;
    }

    /**
     * Test to see if a value is an expectation object. A useful utility method.
     *
     * @param mixed $expectation    Hopefully an Expectation class.
     *
     * @return bool              True if descended from this class.
     */
    public static function isExpectation($expectation)
    {
        return is_object($expectation) && (
            is_a($expectation, 'SimpleExpectation') ||
            is_a($expectation, 'ReferenceExpectation')
        );
    }
}

/**
 * A wildcard expectation always matches.
 */
class AnythingExpectation extends SimpleExpectation
{
    /**
     * Tests the expectation. Always true.
     *
     * @param mixed $compare  Ignored.
     *
     * @return bool        True.
     */
    public function test($compare)
    {
        return true;
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

        return 'Anything always matches [' . $dumper->describeValue($compare) . ']';
    }
}

/**
 * An expectation that never matches.
 */
class FailedExpectation extends SimpleExpectation
{
    /**
     * Tests the expectation. Always false.
     *
     * @param mixed $compare  Ignored.
     *
     * @return bool        True.
     */
    public function test($compare)
    {
        return false;
    }

    /**
     * Returns a human readable test message.
     *
     * @param mixed $compare      Comparison value.
     *
     * @return string             Description of failure.
     */
    public function testMessage($compare)
    {
        $dumper = $this->getDumper();

        return 'Failed expectation never matches [' . $dumper->describeValue($compare) . ']';
    }
}

/**
 * An expectation that passes on boolean true.
 */
class TrueExpectation extends SimpleExpectation
{
    /**
     * Tests the expectation.
     *
     * @param mixed $compare  Should be true.
     *
     * @return bool        True on match.
     */
    public function test($compare)
    {
        return (boolean) $compare;
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

        return 'Expected true, got [' . $dumper->describeValue($compare) . ']';
    }
}

/**
 * An expectation that passes on boolean false.
 */
class FalseExpectation extends SimpleExpectation
{
    /**
     * Tests the expectation.
     *
     * @param mixed $compare  Should be false.
     *
     * @return bool        True on match.
     */
    public function test($compare)
    {
        return ! (boolean) $compare;
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

        return 'Expected false, got [' . $dumper->describeValue($compare) . ']';
    }
}

/**
 * Test for equality.
 */
class EqualExpectation extends SimpleExpectation
{
    private $value;

    /**
     * Sets the value to compare against.
     *
     * @param mixed $value        Test value to match.
     * @param string $message     Customised message on failure.
     */
    public function __construct($value, $message = '%s')
    {
        parent::__construct($message);
        $this->value = $value;
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

        if ($this->test($compare)) {
            return 'Equal expectation [' . $dumper->describeValue($this->value) . ']';
        } else {
            return 'Equal expectation fails ' .
                    $dumper->describeDifference($this->value, $compare);
        }
    }

    /**
     * Accessor for comparison value.
     *
     * @return mixed       Held value to compare with.
     */
    protected function getValue()
    {
        return $this->value;
    }
}

/**
 * Test for inequality.
 */
class NotEqualExpectation extends EqualExpectation
{
    /**
     * Sets the value to compare against.
     *
     * @param mixed $value       Test value to match.
     * @param string $message    Customised message on failure.
     */
    public function __construct($value, $message = '%s')
    {
        parent::__construct($value, $message);
    }

    /**
     * Tests the expectation. True if it differs from the held value.
     *
     * @param mixed $compare        Comparison value.
     *
     * @return bool              True if correct.
     */
    public function test($compare)
    {
        return ! parent::test($compare);
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
        if ($this->test($compare)) {
            return 'Not equal expectation passes ' .
                    $dumper->describeDifference($this->getValue(), $compare);
        } else {
            return 'Not equal expectation fails [' .
                    $dumper->describeValue($this->getValue()) .
                    '] matches';
        }
    }
}

/**
 * Test for being within a range.
 */
class WithinMarginExpectation extends SimpleExpectation
{
    private $upper;
    private $lower;

    /**
     * Sets the value to compare against and the fuzziness of the match.
     * Used for comparing floating point values.
     *
     * @param mixed $value        Test value to match.
     * @param mixed $margin       Fuzziness of match.
     * @param string $message     Customised message on failure.
     */
    public function __construct($value, $margin, $message = '%s')
    {
        parent::__construct($message);
        $this->upper = $value + $margin;
        $this->lower = $value - $margin;
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
        return (($compare <= $this->upper) && ($compare >= $this->lower));
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
        if ($this->test($compare)) {
            return $this->withinMessage($compare);
        } else {
            return $this->outsideMessage($compare);
        }
    }

    /**
     * Creates a the message for being within the range.
     *
     * @param mixed $compare        Value being tested.
     */
    protected function withinMessage($compare)
    {
        $dumper = $this->getDumper();

        return 'Within expectation [' . $dumper->describeValue($this->lower) . '] and [' .
                $dumper->describeValue($this->upper) . ']';
    }

    /**
     * Creates a the message for being within the range.
     *
     * @param mixed $compare        Value being tested.
     */
    protected function outsideMessage($compare)
    {
        $dumper = $this->getDumper();

        if ($compare > $this->upper) {
            return 'Outside expectation ' .
                    $dumper->describeDifference($compare, $this->upper);
        } else {
            return 'Outside expectation ' .
                    $dumper->describeDifference($compare, $this->lower);
        }
    }
}

/**
 * Test for being outside of a range.
 */
class OutsideMarginExpectation extends WithinMarginExpectation
{
    /**
     * Sets the value to compare against and the fuzziness of the match. Used for comparing floating
     * point values.
     *
     * @param mixed $value        Test value to not match.
     * @param mixed $margin       Fuzziness of match.
     * @param string $message     Customised message on failure.
     */
    public function __construct($value, $margin, $message = '%s')
    {
        parent::__construct($value, $margin, $message);
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
        return ! parent::test($compare);
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
        if (! $this->test($compare)) {
            return $this->withinMessage($compare);
        } else {
            return $this->outsideMessage($compare);
        }
    }
}

/**
 * Test for reference.
 */
class ReferenceExpectation
{
    private $message;
    private $value;

    /**
     * Sets the reference value to compare against.
     *
     * @param mixed $value       Test reference to match.
     * @param string $message    Customised message on failure.
     */
    public function __construct(&$value, $message = '%s')
    {
        $this->message = $message;
        $this->value   = &$value;
    }

    /**
     * Tests the expectation. True if it exactly references the held value.
     *
     * @param mixed $compare        Comparison reference.
     *
     * @return bool              True if correct.
     */
    public function test(&$compare)
    {
        return SimpleTestCompatibility::isReference($this->value, $compare);
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
        if ($this->test($compare)) {
            return 'Reference expectation [' . $this->dumper->describeValue($this->value) . ']';
        } else {
            return 'Reference expectation fails ' .
                    $this->dumper->describeDifference($this->value, $compare);
        }
    }

    /**
     * Overlays the generated message onto the stored user message.
     * An additional message can be interjected.
     *
     * @param mixed $compare        Comparison value.
     * @param SimpleDumper $dumper  For formatting the results.
     *
     * @return string               Description of success or failure.
     */
    public function overlayMessage($compare, $dumper)
    {
        $this->dumper = $dumper;

        return sprintf($this->message, $this->testMessage($compare));
    }

    /**
     * Accessor for the dumper.
     *
     * @return SimpleDumper    Current value dumper.
     */
    protected function getDumper()
    {
        if (! $this->dumper) {
            $dumper = new SimpleDumper();

            return $dumper;
        }

        return $this->dumper;
    }
}

/**
 * Test for identity.
 */
class IdenticalExpectation extends EqualExpectation
{
    /**
     * Sets the value to compare against.
     *
     * @param mixed $value       Test value to match.
     * @param string $message    Customised message on failure.
     */
    public function __construct($value, $message = '%s')
    {
        parent::__construct($value, $message);
    }

    /**
     * Tests the expectation. True if it exactly matches the held value.
     *
     * @param mixed $compare        Comparison value.
     *
     * @return bool              True if correct.
     */
    public function test($compare)
    {
        return SimpleTestCompatibility::isIdentical($this->getValue(), $compare);
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
        if ($this->test($compare)) {
            return 'Identical expectation [' . $dumper->describeValue($this->getValue()) . ']';
        } else {
            return 'Identical expectation [' . $dumper->describeValue($this->getValue()) .
                    '] fails with [' .
                    $dumper->describeValue($compare) . '] ' .
                    $dumper->describeDifference($this->getValue(), $compare, TYPE_MATTERS);
        }
    }
}

/**
 * Test for non-identity.
 */
class NotIdenticalExpectation extends IdenticalExpectation
{
    /**
     * Sets the value to compare against.
     *
     * @param mixed $value        Test value to match.
     * @param string $message     Customised message on failure.
     */
    public function __construct($value, $message = '%s')
    {
        parent::__construct($value, $message);
    }

    /**
     * Tests the expectation. True if it differs from the held value.
     *
     * @param mixed $compare        Comparison value.
     *
     * @return bool              True if correct.
     */
    public function test($compare)
    {
        return ! parent::test($compare);
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
        if ($this->test($compare)) {
            return 'Not identical expectation passes ' .
                    $dumper->describeDifference($this->getValue(), $compare, TYPE_MATTERS);
        } else {
            return 'Not identical expectation [' . $dumper->describeValue($this->getValue()) . '] matches';
        }
    }
}

/**
 * Test for a pattern using Perl regex rules.
 */
class PatternExpectation extends SimpleExpectation
{
    private $pattern;

    /**
     * Sets the value to compare against.
     *
     * @param string $pattern    Pattern to search for.
     * @param string $message    Customised message on failure.
     */
    public function __construct($pattern, $message = '%s')
    {
        parent::__construct($message);
        $this->pattern = $pattern;
    }

    /**
     * Accessor for the pattern.
     *
     * @return string       Perl regex as string.
     */
    protected function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Tests the expectation. True if the Perl regex matches the comparison value.
     *
     * @param string $compare        Comparison value.
     *
     * @return bool               True if correct.
     */
    public function test($compare)
    {
        return (boolean) preg_match($this->getPattern(), $compare);
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
        if ($this->test($compare)) {
            return $this->describePatternMatch($this->getPattern(), $compare);
        } else {
            $dumper = $this->getDumper();

            return 'Pattern [' . $this->getPattern() . '] not detected in [' . $dumper->describeValue($compare) . ']';
        }
    }

    /**
     * Describes a pattern match including the string found and it's position.
     *
     * @param string $pattern        Regex to match against.
     * @param string $subject        Subject to search.
     */
    protected function describePatternMatch($pattern, $subject)
    {
        preg_match($pattern, $subject, $matches);
        $position = strpos($subject, $matches[0]);
        $dumper   = $this->getDumper();

        return "Pattern [$pattern] detected at character [$position] in [" .
                $dumper->describeValue($subject) . '] as [' .
                $matches[0] . '] in region [' .
                $dumper->clipString($subject, 100, $position) . ']';
    }
}

/**
 * Fail if a pattern is detected within the comparison.
 */
class NoPatternExpectation extends PatternExpectation
{
    /**
     * Sets the reject pattern
     *
     * @param string $pattern    Pattern to search for.
     * @param string $message    Customised message on failure.
     */
    public function __construct($pattern, $message = '%s')
    {
        parent::__construct($pattern, $message);
    }

    /**
     * Tests the expectation. False if the Perl regex matches the comparison value.
     *
     * @param string $compare        Comparison value.
     *
     * @return bool               True if correct.
     */
    public function test($compare)
    {
        return ! parent::test($compare);
    }

    /**
     * Returns a human readable test message.
     *
     * @param string $compare      Comparison value.
     *
     * @return string              Description of success or failure.
     */
    public function testMessage($compare)
    {
        if ($this->test($compare)) {
            $dumper = $this->getDumper();

            return 'Pattern [' . $this->getPattern() .
                    '] not detected in [' .
                    $dumper->describeValue($compare) . ']';
        } else {
            return $this->describePatternMatch($this->getPattern(), $compare);
        }
    }
}

/**
 * Tests either type or class name if it's an object.
 */
class IsAExpectation extends SimpleExpectation
{
    private $type;

    /**
     * Sets the type to compare with.
     *
     * @param string $type       Type or class name.
     * @param string $message    Customised message on failure.
     */
    public function __construct($type, $message = '%s')
    {
        parent::__construct($message);
        $this->type = $type;
    }

    /**
     * Accessor for type to check against.
     *
     * @return string    Type or class name.
     */
    protected function getType()
    {
        return $this->type;
    }

    /**
     * Tests the expectation. True if the type or class matches the string value.
     *
     * @param string $compare        Comparison value.
     *
     * @return bool               True if correct.
     */
    public function test($compare)
    {
        if (is_object($compare)) {
            return is_a($compare, $this->type);
        } else {
            $function = 'is_' . $this->canonicalType($this->type);
            if (is_callable($function)) {
                return $function($compare);
            }

            return false;
        }
    }

    /**
     * forces type name into a is_*() match.
     *
     * @param string $type        User type.
     *
     * @return string             Simpler type.
     */
    protected function canonicalType($type)
    {
        $type = strtolower($type);
        $map  = array('boolean' => 'bool');
        if (isset($map[$type])) {
            $type = $map[$type];
        }

        return $type;
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

        return 'Value [' . $dumper->describeValue($compare) .
                '] should be type [' . $this->type . ']';
    }
}

/**
 * Tests either type or class name if it's an object.
 * Will succeed if the type does not match.
 */
class NotAExpectation extends IsAExpectation
{
    private $type;

    /**
     * Sets the type to compare with.
     *
     * @param string $type       Type or class name.
     * @param string $message    Customised message on failure.
     */
    public function __construct($type, $message = '%s')
    {
        parent::__construct($type, $message);
    }

    /**
     * Tests the expectation. False if the type or class matches the string value.
     *
     * @param string $compare        Comparison value.
     *
     * @return bool               True if different.
     */
    public function test($compare)
    {
        return ! parent::test($compare);
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

        return 'Value [' . $dumper->describeValue($compare) .
                '] should not be type [' . $this->getType() . ']';
    }
}

/**
 * Tests for existance of a method in an object
 */
class MethodExistsExpectation extends SimpleExpectation
{
    private $method;

    /**
     * Sets the value to compare against.
     *
     * @param string $method     Method to check.
     * @param string $message    Customised message on failure.
     */
    public function __construct($method, $message = '%s')
    {
        parent::__construct($message);
        $this->method = &$method;
    }

    /**
     * Tests the expectation. True if the method exists in the test object.
     *
     * @param string $compare        Comparison method name.
     *
     * @return bool               True if correct.
     */
    public function test($compare)
    {
        return (boolean) (is_object($compare) && method_exists($compare, $this->method));
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
        if (! is_object($compare)) {
            return 'No method on non-object [' . $dumper->describeValue($compare) . ']';
        }
        $method = $this->method;

        return 'Object [' . $dumper->describeValue($compare) .
                "] should contain method [$method]";
    }
}

/**
 * Compares an object member's value even if private.
 */
class MemberExpectation extends IdenticalExpectation
{
    private $name;

    /**
     * Sets the value to compare against.
     *
     * @param string $method     Method to check.
     * @param string $message    Customised message on failure.
     */
    public function __construct($name, $expected)
    {
        $this->name = $name;
        parent::__construct($expected);
    }

    /**
     * Tests the expectation. True if the property value is identical.
     *
     * @param object $actual         Comparison object.
     *
     * @return bool               True if identical.
     */
    public function test($actual)
    {
        if (! is_object($actual)) {
            return false;
        }

        return parent::test($this->getProperty($this->name, $actual));
    }

    /**
     * Returns a human readable test message.
     *
     * @param mixed $compare      Comparison value.
     *
     * @return string             Description of success or failure.
     */
    public function testMessage($actual)
    {
        return parent::testMessage($this->getProperty($this->name, $actual));
    }

    /**
     * Extracts the member value even if private using reflection.
     *
     * @param string $name        Property name.
     * @param object $object      Object to read.
     *
     * @return mixed              Value of property.
     */
    private function getProperty($name, $object)
    {
        $reflection = new ReflectionObject($object);
        $property   = $reflection->getProperty($name);
        if (method_exists($property, 'setAccessible')) {
            $property->setAccessible(true);
        }
        try {
            return $property->getValue($object);
        } catch (ReflectionException $e) {
            return $this->getPrivatePropertyNoMatterWhat($name, $object);
        }
    }

    /**
     * Extracts a private member's value when reflection won't play ball.
     *
     * @param string $name        Property name.
     * @param object $object      Object to read.
     *
     * @return mixed              Value of property.
     */
    private function getPrivatePropertyNoMatterWhat($name, $object)
    {
        foreach ((array) $object as $mangled_name => $value) {
            if ($this->unmangle($mangled_name) == $name) {
                return $value;
            }
        }
    }

    /**
     * Removes crud from property name after it's been converted to an array.
     *
     * @param string $mangled     Name from array cast.
     *
     * @return string             Cleaned up name.
     */
    public function unmangle($mangled)
    {
        $parts = preg_split('/[^a-zA-Z0-9_\x7f-\xff]+/', $mangled);

        return array_pop($parts);
    }
}
