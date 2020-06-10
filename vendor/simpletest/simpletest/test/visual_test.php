<?php

// NOTE:
// Some of these tests are designed to fail! Do not be alarmed.
//                         ----------------

// The following tests are a bit hacky. Whilst Kent Beck tried to
// build a unit tester with a unit tester, I am not that brave.
// Instead I have just hacked together odd test scripts until
// I have enough of a tester to procede more formally.
//
// The proper tests start in all_tests.php
require_once '../unit_tester.php';
require_once '../shell_tester.php';
require_once '../mock_objects.php';
require_once '../reporter.php';
require_once '../xml.php';

class TestDisplayClass
{
    private $a;

    public function __construct($a)
    {
        $this->a = $a;
    }
}

class PassingUnitTestCaseOutput extends UnitTestCase
{
    public function testOfResults()
    {
        $this->pass('Pass');
    }

    public function testTrue()
    {
        $this->assertTrue(true);
    }

    public function testFalse()
    {
        $this->assertFalse(false);
    }

    public function testExpectation()
    {
        $expectation = new EqualExpectation(25, 'My expectation message: %s');
        $this->assert($expectation, 25, 'My assert message : %s');
    }

    public function testNull()
    {
        $this->assertNull(null, '%s -> Pass');
        $this->assertNotNull(false, '%s -> Pass');
    }

    public function testType()
    {
        $this->assertIsA('hello', 'string', '%s -> Pass');
        $this->assertIsA($this, 'PassingUnitTestCaseOutput', '%s -> Pass');
        $this->assertIsA($this, 'UnitTestCase', '%s -> Pass');
    }

    public function testTypeEquality()
    {
        $this->assertEqual('0', 0, '%s -> Pass');
    }

    public function testNullEquality()
    {
        $this->assertNotEqual(null, 1, '%s -> Pass');
        $this->assertNotEqual(1, null, '%s -> Pass');
    }

    public function testIntegerEquality()
    {
        $this->assertNotEqual(1, 2, '%s -> Pass');
    }

    public function testStringEquality()
    {
        $this->assertEqual('a', 'a', '%s -> Pass');
        $this->assertNotEqual('aa', 'ab', '%s -> Pass');
    }

    public function testHashEquality()
    {
        $this->assertEqual(array('a' => 'A', 'b' => 'B'), array('b' => 'B', 'a' => 'A'), '%s -> Pass');
    }

    public function testWithin()
    {
        $this->assertWithinMargin(5, 5.4, 0.5, '%s -> Pass');
    }

    public function testOutside()
    {
        $this->assertOutsideMargin(5, 5.6, 0.5, '%s -> Pass');
    }

    public function testStringIdentity()
    {
        $a = 'fred';
        $b = $a;
        $this->assertIdentical($a, $b, '%s -> Pass');
    }

    public function testTypeIdentity()
    {
        $a = '0';
        $b = 0;
        $this->assertNotIdentical($a, $b, '%s -> Pass');
    }

    public function testNullIdentity()
    {
        $this->assertNotIdentical(null, 1, '%s -> Pass');
        $this->assertNotIdentical(1, null, '%s -> Pass');
    }

    public function testHashIdentity()
    {
    }

    public function testObjectEquality()
    {
        $this->assertEqual(new TestDisplayClass(4), new TestDisplayClass(4), '%s -> Pass');
        $this->assertNotEqual(new TestDisplayClass(4), new TestDisplayClass(5), '%s -> Pass');
    }

    public function testObjectIndentity()
    {
        $this->assertIdentical(new TestDisplayClass(false), new TestDisplayClass(false), '%s -> Pass');
        $this->assertNotIdentical(new TestDisplayClass(false), new TestDisplayClass(0), '%s -> Pass');
    }

    public function testReference()
    {
        $a = 'fred';
        $b = &$a;
        $this->assertReference($a, $b, '%s -> Pass');
    }

    /*public function testCloneOnDifferentObjects()
    {
        // test for copy object; both objects represent the same memory address
        $object1 = new stdClass;
        $object1->name = 'Object 1';
        $object2 = $object1; // copy the object

        $this->assertSame($object1, $object2);
        $this->assertSame($object1->name, 'Object 1');
        $this->assertSame($object2->name, 'Object 1');

        // test for clone object; both objects are independent
        $object3 = clone $object1;
        // after clone they still have equal values
        $this->assertSame($object1->name, 'Object 1');
        $this->assertSame($object3->name, 'Object 1');
        // modify values
        $object1->name = 'Still Object 1';
        $object3->name = 'Object 3';
        // test for values
        $this->assertSame($object1->name, 'Still Object 1');
        $this->assertSame($object3->name, 'Object 3');
        // finally, test difference of cloned objects
        $this->assertClone($object1, $object2, '%s -> Pass');
    }*/

    public function testPatterns()
    {
        $this->assertPattern('/hello/i', 'Hello there', '%s -> Pass');
        $this->assertNoPattern('/hello/', 'Hello there', '%s -> Pass');
    }

    public function testLongStrings()
    {
        $text = '';
        for ($i = 0; $i < 10; $i++) {
            $text .= '0123456789';
        }
        $this->assertEqual($text, $text);
    }
}

class FailingUnitTestCaseOutput extends UnitTestCase
{
    public function testOfResults()
    {
        $this->fail('Fail');        // Fail.
    }

    public function testTrue()
    {
        $this->assertTrue(false);        // Fail.
    }

    public function testFalse()
    {
        $this->assertFalse(true);        // Fail.
    }

    public function testExpectation()
    {
        $expectation = new EqualExpectation(25, 'My expectation message: %s');
        $this->assert($expectation, 24, 'My assert message : %s');        // Fail.
    }

    public function testNull()
    {
        $this->assertNull(false, '%s -> Fail');        // Fail.
        $this->assertNotNull(null, '%s -> Fail');        // Fail.
    }

    public function testType()
    {
        $this->assertIsA(14, 'string', '%s -> Fail');        // Fail.
        $this->assertIsA(14, 'TestOfUnitTestCaseOutput', '%s -> Fail');        // Fail.
        $this->assertIsA($this, 'TestReporter', '%s -> Fail');        // Fail.
    }

    public function testTypeEquality()
    {
        $this->assertNotEqual('0', 0, '%s -> Fail');        // Fail.
    }

    public function testNullEquality()
    {
        $this->assertEqual(null, 1, '%s -> Fail');        // Fail.
        $this->assertEqual(1, null, '%s -> Fail');        // Fail.
    }

    public function testIntegerEquality()
    {
        $this->assertEqual(1, 2, '%s -> Fail');        // Fail.
    }

    public function testStringEquality()
    {
        $this->assertNotEqual('a', 'a', '%s -> Fail');    // Fail.
        $this->assertEqual('aa', 'ab', '%s -> Fail');        // Fail.
    }

    public function testHashEquality()
    {
        $this->assertEqual(array('a' => 'A', 'b' => 'B'), array('b' => 'B', 'a' => 'Z'), '%s -> Fail');
    }

    public function testWithin()
    {
        $this->assertWithinMargin(5, 5.6, 0.5, '%s -> Fail');   // Fail.
    }

    public function testOutside()
    {
        $this->assertOutsideMargin(5, 5.4, 0.5, '%s -> Fail');   // Fail.
    }

    public function testStringIdentity()
    {
        $a = 'fred';
        $b = $a;
        $this->assertNotIdentical($a, $b, '%s -> Fail');       // Fail.
    }

    public function testTypeIdentity()
    {
        $a = '0';
        $b = 0;
        $this->assertIdentical($a, $b, '%s -> Fail');        // Fail.
    }

    public function testNullIdentity()
    {
        $this->assertIdentical(null, 1, '%s -> Fail');        // Fail.
        $this->assertIdentical(1, null, '%s -> Fail');        // Fail.
    }

    public function testHashIdentity()
    {
        $this->assertIdentical(array('a' => 'A', 'b' => 'B'), array('b' => 'B', 'a' => 'A'), '%s -> fail');        // Fail.
    }

    public function testObjectEquality()
    {
        $this->assertNotEqual(new TestDisplayClass(4), new TestDisplayClass(4), '%s -> Fail');    // Fail.
        $this->assertEqual(new TestDisplayClass(4), new TestDisplayClass(5), '%s -> Fail');        // Fail.
    }

    public function testObjectIndentity()
    {
        $this->assertNotIdentical(new TestDisplayClass(false), new TestDisplayClass(false), '%s -> Fail');    // Fail.
        $this->assertIdentical(new TestDisplayClass(false), new TestDisplayClass(0), '%s -> Fail');        // Fail.
    }

    public function testReference()
    {
        $a = 'fred';
        $b = &$a;
        $this->assertClone($a, $b, '%s -> Fail');        // Fail.
    }

    public function testCloneOnDifferentObjects()
    {
        $a = 'fred';
        $b = $a;
        $c = 'Hello';
        $this->assertClone($a, $c, '%s -> Fail');        // Fail.
    }

    public function testPatterns()
    {
        $this->assertPattern('/hello/', 'Hello there', '%s -> Fail');            // Fail.
        $this->assertNoPattern('/hello/i', 'Hello there', '%s -> Fail');      // Fail.
    }

    public function testLongStrings()
    {
        $text = '';
        for ($i = 0; $i < 10; $i++) {
            $text .= '0123456789';
        }
        $this->assertEqual($text . $text, $text . 'a' . $text);        // Fail.
    }
}

class Dummy
{
    public function __construct()
    {
    }

    public function a()
    {
    }
}
Mock::generate('Dummy');

class TestOfMockObjectsOutput extends UnitTestCase
{
    public function testCallCounts()
    {
        $dummy = new MockDummy();
        $dummy->expectCallCount('a', 1, 'My message: %s');
        $dummy->a();
        $dummy->a();
    }

    public function testMinimumCallCounts()
    {
        $dummy = new MockDummy();
        $dummy->expectMinimumCallCount('a', 2, 'My message: %s');
        $dummy->a();
        $dummy->a();
    }

    public function testEmptyMatching()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array());
        $dummy->a();
        $dummy->a(null);        // Fail.
    }

    public function testEmptyMatchingWithCustomMessage()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array(), 'My expectation message: %s');
        $dummy->a();
        $dummy->a(null);        // Fail.
    }

    public function testNullMatching()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array(null));
        $dummy->a(null);
        $dummy->a();        // Fail.
    }

    public function testBooleanMatching()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array(true, false));
        $dummy->a(true, false);
        $dummy->a(true, true);        // Fail.
    }

    public function testIntegerMatching()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array(32, 33));
        $dummy->a(32, 33);
        $dummy->a(32, 34);        // Fail.
    }

    public function testFloatMatching()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array(3.2, 3.3));
        $dummy->a(3.2, 3.3);
        $dummy->a(3.2, 3.4);        // Fail.
    }

    public function testStringMatching()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array('32', '33'));
        $dummy->a('32', '33');
        $dummy->a('32', '34');        // Fail.
    }

    public function testEmptyMatchingWithCustomExpectationMessage()
    {
        $dummy = new MockDummy();
        $dummy->expect(
                'a',
                array(new EqualExpectation('A', 'My part expectation message: %s')),
                'My expectation message: %s');
        $dummy->a('A');
        $dummy->a('B');        // Fail.
    }

    public function testArrayMatching()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array(array(32), array(33)));
        $dummy->a(array(32), array(33));
        $dummy->a(array(32), array('33'));        // Fail.
    }

    public function testObjectMatching()
    {
        $a     = new Dummy();
        $a->a  = 'a';
        $b     = new Dummy();
        $b->b  = 'b';
        $dummy = new MockDummy();
        $dummy->expect('a', array($a, $b));
        $dummy->a($a, $b);
        $dummy->a($a, $a);        // Fail.
    }

    public function testBigList()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array(false, 0, 1, 1.0));
        $dummy->a(false, 0, 1, 1.0);
        $dummy->a(true, false, 2, 2.0);        // Fail.
    }
}

class TestOfPastBugs extends UnitTestCase
{
    public function testMixedTypes()
    {
        $this->assertEqual(array(), null, '%s -> Pass');
        $this->assertIdentical(array(), null, '%s -> Fail');    // Fail.
    }

    public function testMockWildcards()
    {
        $dummy = new MockDummy();
        $dummy->expect('a', array('*', array(33)));
        $dummy->a(array(32), array(33));
        $dummy->a(array(32), array('33'));        // Fail.
    }
}

class TestOfVisualShell extends ShellTestCase
{
    public function testDump()
    {
        $this->execute('ls');
        $this->dumpOutput();
        $this->execute('dir');
        $this->dumpOutput();
    }

    public function testDumpOfList()
    {
        $this->execute('ls');
        $this->dump($this->getOutputAsList());
    }
}

class PassesAsWellReporter extends HtmlReporter
{
    protected function getCss()
    {
        return parent::getCss() . ' .pass { color: darkgreen; }';
    }

    public function paintPass($message)
    {
        parent::paintPass($message);
        print '<span class="pass">Pass</span>: ';
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(' -&gt; ', $breadcrumb);
        print ' -&gt; ' . htmlentities($message) . "<br />\n";
    }

    public function paintSignal($type, $payload)
    {
        print "<span class=\"fail\">$type</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(' -&gt; ', $breadcrumb);
        print ' -&gt; ' . htmlentities(serialize($payload)) . "<br />\n";
    }
}

class TestOfSkippingNoMatterWhat extends UnitTestCase
{
    public function skip()
    {
        $this->skipIf(true, 'Always skipped -> %s');
    }

    public function testFail()
    {
        $this->fail('This really shouldn\'t have happened');
    }
}

class TestOfSkippingOrElse extends UnitTestCase
{
    public function skip()
    {
        $this->skipUnless(false, 'Always skipped -> %s');
    }

    public function testFail()
    {
        $this->fail('This really shouldn\'t have happened');
    }
}

class TestOfSkippingTwiceOver extends UnitTestCase
{
    public function skip()
    {
        $this->skipIf(true, 'First reason -> %s');
        $this->skipIf(true, 'Second reason -> %s');
    }

    public function testFail()
    {
        $this->fail('This really shouldn\'t have happened');
    }
}

class TestThatShouldNotBeSkipped extends UnitTestCase
{
    public function skip()
    {
        $this->skipIf(false);
        $this->skipUnless(true);
    }

    public function testFail()
    {
        $this->fail('We should see this message');
    }

    public function testPass()
    {
        $this->pass('We should see this message');
    }
}

$test = new TestSuite('Visual test with 46 passes, 47 fails and 0 exceptions');
$test->add(new PassingUnitTestCaseOutput());
$test->add(new FailingUnitTestCaseOutput());
$test->add(new TestOfMockObjectsOutput());
$test->add(new TestOfPastBugs());
$test->add(new TestOfVisualShell());
$test->add(new TestOfSkippingNoMatterWhat());
$test->add(new TestOfSkippingOrElse());
$test->add(new TestOfSkippingTwiceOver());
$test->add(new TestThatShouldNotBeSkipped());

if (isset($_GET['xml']) || in_array('xml', (isset($argv) ? $argv : array()))) {
    $reporter = new XmlReporter();
} elseif (TextReporter::inCli()) {
    $reporter = new TextReporter();
} else {
    $reporter = new PassesAsWellReporter();
}
if (isset($_GET['dry']) || in_array('dry', (isset($argv) ? $argv : array()))) {
    $reporter->makeDry();
}
exit($test->run($reporter) ? 0 : 1);
