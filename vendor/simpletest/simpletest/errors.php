<?php

require_once __DIR__ . '/invoker.php';
require_once __DIR__ . '/test_case.php';
require_once __DIR__ . '/expectation.php';

/**
 * Extension that traps errors into an error queue.
 */
class SimpleErrorTrappingInvoker extends SimpleInvokerDecorator
{
    /**
     * Stores the invoker to wrap.
     *
     * @param SimpleInvoker $invoker  Test method runner.
     */
    public function __construct($invoker)
    {
        parent::__construct($invoker);
    }

    /**
     * Invokes a test method and dispatches any untrapped errors.
     * Called back from the visiting runner.
     *
     * @param string $method    Test method to call.
     */
    public function invoke($method)
    {
        $queue = $this->createErrorQueue();
        set_error_handler('SimpleTestErrorHandler');
        parent::invoke($method);
        restore_error_handler();
        $queue->tally();
    }

    /**
     * Wires up the error queue for a single test.
     *
     * @return SimpleErrorQueue    Queue connected to the test.
     */
    protected function createErrorQueue()
    {
        $context = SimpleTest::getContext();
        $test    = $this->getTestCase();
        $queue   = $context->get('SimpleErrorQueue');
        $queue->setTestCase($test);

        return $queue;
    }
}

/**
 * Error queue used to record trapped errors.
 */
class SimpleErrorQueue
{
    private $queue;
    private $expectation_queue;
    private $test;

    /**
     * Starts with an empty queue.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Discards the contents of the error queue.
     */
    public function clear()
    {
        $this->queue             = array();
        $this->expectation_queue = array();
    }

    /**
     * Sets the currently running test case.
     *
     * @param SimpleTestCase $test    Test case to send messages to.
     */
    public function setTestCase($test)
    {
        $this->test = $test;
    }

    /**
     * Sets up an expectation of an error.
     * If this is not fulfilled at the end of the test, a failure will occour.
     * If the error does happen, then this will cancel it out and send a pass message.
     *
     * @param SimpleExpectation $expected    Expected error match.
     * @param string $message                Message to display.
     */
    public function expectError($expected, $message)
    {
        array_push($this->expectation_queue, array($expected, $message));
    }

    /**
     * Adds an error to the front of the queue.
     *
     * @param int $severity       PHP error code.
     * @param string $content         Text of error.
     * @param string $filename        File error occoured in.
     * @param int $line           Line number of error.
     */
    public function add($severity, $content, $filename, $line)
    {
        $content = str_replace('%', '%%', $content);
        $this->testLatestError($severity, $content, $filename, $line);
    }

    /**
     * Any errors still in the queue are sent to the test case.
     * Any unfulfilled expectations trigger failures.
     */
    public function tally()
    {
        while (list($severity, $message, $file, $line) = $this->extract()) {
            $severity = $this->getSeverityAsString($severity);
            $this->test->error($severity, $message, $file, $line);
        }
        while (list($expected, $message) = $this->extractExpectation()) {
            $this->test->assert($expected, false, '%s -> Expected error not caught');
        }
    }

    /**
     * Tests the error against the most recent expected error.
     *
     * @param int $severity       PHP error code.
     * @param string $content         Text of error.
     * @param string $filename        File error occoured in.
     * @param int $line           Line number of error.
     */
    protected function testLatestError($severity, $content, $filename, $line)
    {
        $expectation = $this->extractExpectation();

        if ($expectation === false) {
            $this->test->error($severity, $content, $filename, $line);
        } else {
            list($expected, $message) = $expectation;

            $errorMessage = sprintf(
                'PHP error [%s] severity [%s] in [%s] line [%s]',
                $content, $this->getSeverityAsString($severity), $filename, $line
            );

            $this->test->assert($expected, $content, sprintf($message, '%s' . $errorMessage));
        }
    }

    /**
     * Pulls the earliest error from the queue.
     *
     * The list of error informations contains:
     *  - severity as the PHP error code,
     *  - the error message,
     *  - the file with the error,
     *  - the line number and
     *  - a list of PHP super global arrays.
     *
     * @return  mixed    False if none, or a list of error information.
     */
    public function extract()
    {
        if (count($this->queue)) {
            return array_shift($this->queue);
        }

        return false;
    }

    /**
     * Pulls the earliest expectation from the queue.
     *
     * @return     SimpleExpectation    False if none.
     */
    protected function extractExpectation()
    {
        if (count($this->expectation_queue)) {
            return array_shift($this->expectation_queue);
        }

        return false;
    }

    /**
     * Converts an error code into it's string representation.
     *
     * @param $severity  PHP integer error code.
     *
     * @return String version of error code.
     */
    public static function getSeverityAsString($severity)
    {
        static $map = array(
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',   # PHP 5.2
            E_DEPRECATED        => 'E_DEPRECATED',          # PHP 5.3
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',     # PHP 5.3
            E_ALL               => 'E_ALL'
        );

        return $map[$severity];
    }
}

/**
 * Error handler that simply stashes any errors into the global error queue.
 * Simulates the existing behaviour with respect to logging errors,
 * but this feature may be removed in future.
 *
 * @param $severity    PHP error code.
 * @param $message     Text of error.
 * @param $file        File error occoured in.
 * @param $line        Line number of error.
 * @param $context     Error Context.
 */
function SimpleTestErrorHandler($severity, $message, $file = null, $line = null, $context = null )
{
    $severity = $severity & error_reporting();
    if ($severity) {
        restore_error_handler();

        $queue = SimpleTest::getContext()->get('SimpleErrorQueue');
        $queue->add($severity, $message, $file, $line);

        set_error_handler('SimpleTestErrorHandler');
    }

    return true;
}
