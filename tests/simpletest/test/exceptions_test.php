<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../exceptions.php');
require_once(dirname(__FILE__) . '/../expectation.php');
require_once(dirname(__FILE__) . '/../test_case.php');
Mock::generate('SimpleTestCase');
Mock::generate('SimpleExpectation');

class MyTestException extends Exception
{
}
class HigherTestException extends MyTestException
{
}
class OtherTestException extends Exception
{
}

class TestOfExceptionExpectation extends UnitTestCase
{
    public function testExceptionClassAsStringWillMatchExceptionsRootedOnThatClass()
    {
        $expectation = new ExceptionExpectation('MyTestException');
        $this->assertTrue($expectation->test(new MyTestException()));
        $this->assertTrue($expectation->test(new HigherTestException()));
        $this->assertFalse($expectation->test(new OtherTestException()));
    }

    public function testMatchesClassAndMessageWhenExceptionExpected()
    {
        $expectation = new ExceptionExpectation(new MyTestException('Hello'));
        $this->assertTrue($expectation->test(new MyTestException('Hello')));
        $this->assertFalse($expectation->test(new HigherTestException('Hello')));
        $this->assertFalse($expectation->test(new OtherTestException('Hello')));
        $this->assertFalse($expectation->test(new MyTestException('Goodbye')));
        $this->assertFalse($expectation->test(new MyTestException()));
    }

    public function testMessagelessExceptionMatchesOnlyOnClass()
    {
        $expectation = new ExceptionExpectation(new MyTestException());
        $this->assertTrue($expectation->test(new MyTestException()));
        $this->assertFalse($expectation->test(new HigherTestException()));
    }
}

class TestOfExceptionTrap extends UnitTestCase
{
    public function testNoExceptionsInQueueMeansNoTestMessages()
    {
        $test = new MockSimpleTestCase();
        $test->expectNever('assert');
        $queue = new SimpleExceptionTrap();
        $this->assertFalse($queue->isExpected($test, new Exception()));
    }

    public function testMatchingExceptionGivesTrue()
    {
        $expectation = new MockSimpleExpectation();
        $expectation->setReturnValue('test', true);
        $test = new MockSimpleTestCase();
        $test->setReturnValue('assert', true);
        $queue = new SimpleExceptionTrap();
        $queue->expectException($expectation, 'message');
        $this->assertTrue($queue->isExpected($test, new Exception()));
    }

    public function testMatchingExceptionTriggersAssertion()
    {
        $test = new MockSimpleTestCase();
        $test->expectOnce('assert', array(
                '*',
                new ExceptionExpectation(new Exception()),
                'message'));
        $queue = new SimpleExceptionTrap();
        $queue->expectException(new ExceptionExpectation(new Exception()), 'message');
        $queue->isExpected($test, new Exception());
    }
}

class TestOfCatchingExceptions extends UnitTestCase
{
    public function testCanCatchAnyExpectedException()
    {
        $this->expectException();
        throw new Exception();
    }

    public function testCanMatchExceptionByClass()
    {
        $this->expectException('MyTestException');
        throw new HigherTestException();
    }

    public function testCanMatchExceptionExactly()
    {
        $this->expectException(new Exception('Ouch'));
        throw new Exception('Ouch');
    }

    public function testLastListedExceptionIsTheOneThatCounts()
    {
        $this->expectException('OtherTestException');
        $this->expectException('MyTestException');
        throw new HigherTestException();
    }
}

class TestOfIgnoringExceptions extends UnitTestCase
{
    public function testCanIgnoreAnyException()
    {
        $this->ignoreException();
        throw new Exception();
    }

    public function testCanIgnoreSpecificException()
    {
        $this->ignoreException('MyTestException');
        throw new MyTestException();
    }

    public function testCanIgnoreExceptionExactly()
    {
        $this->ignoreException(new Exception('Ouch'));
        throw new Exception('Ouch');
    }

    public function testIgnoredExceptionsDoNotMaskExpectedExceptions()
    {
        $this->ignoreException('Exception');
        $this->expectException('MyTestException');
        throw new MyTestException();
    }

    public function testCanIgnoreMultipleExceptions()
    {
        $this->ignoreException('MyTestException');
        $this->ignoreException('OtherTestException');
        throw new OtherTestException();
    }
}

class TestOfCallingTearDownAfterExceptions extends UnitTestCase
{
    private $debri = 0;

    public function tearDown()
    {
        $this->debri--;
    }

    public function testLeaveSomeDebri()
    {
        $this->debri++;
        $this->expectException();
        throw new Exception(__FUNCTION__);
    }

    public function testDebriWasRemovedOnce()
    {
        $this->assertEqual($this->debri, 0);
    }
}

class TestOfExceptionThrownInSetUpDoesNotRunTestBody extends UnitTestCase
{
    public function setUp()
    {
        $this->expectException();
        throw new Exception();
    }

    public function testShouldNotBeRun()
    {
        $this->fail('This test body should not be run');
    }

    public function testShouldNotBeRunEither()
    {
        $this->fail('This test body should not be run either');
    }
}

class TestOfExpectExceptionWithSetUp extends UnitTestCase
{
    public function setUp()
    {
        $this->expectException();
    }

    public function testThisExceptionShouldBeCaught()
    {
        throw new Exception();
    }

    public function testJustThrowingMyTestException()
    {
        throw new MyTestException();
    }
}

class TestOfThrowingExceptionsInTearDown extends UnitTestCase
{
    public function tearDown()
    {
        throw new Exception();
    }

    public function testDoesntFatal()
    {
        $this->expectException();
    }
}
