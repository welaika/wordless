<?php

require_once __DIR__ . '/invoker.php';

/**
 * Can receive test events and display them.
 * Display is achieved by making display methods available
 * and visiting the incoming event.
 *
 * @abstract
 */
class SimpleScorer
{
    private $passes;
    private $fails;
    private $exceptions;
    private $is_dry_run;

    /**
     * Starts the test run with no results.
     */
    public function __construct()
    {
        $this->passes     = 0;
        $this->fails      = 0;
        $this->exceptions = 0;
        $this->is_dry_run = false;
    }

    /**
     * Signals that the next evaluation will be a dry run.
     * That is, the structure events will be recorded, but no tests will be run.
     *
     * @param bool $is_dry        Dry run if true.
     */
    public function makeDry($is_dry = true)
    {
        $this->is_dry_run = $is_dry;
    }

    /**
     * The reporter has a veto on what should be run.
     *
     * @param string $test_case_name  name of test case.
     * @param string $method          Name of test method.
     */
    public function shouldInvoke($test_case_name, $method)
    {
        return ! $this->is_dry_run;
    }

    /**
     * Can wrap the invoker in preperation for running a test.
     *
     * @param SimpleInvoker $invoker   Individual test runner.
     *
     * @return SimpleInvoker           Wrapped test runner.
     */
    public function createInvoker($invoker)
    {
        return $invoker;
    }

    /**
     * Accessor for current status.
     * Will be false if there have been any failures or exceptions.
     * Used for command line tools.
     *
     * @return bool        True if no failures.
     */
    public function getStatus()
    {
        if ($this->exceptions + $this->fails > 0) {
            return false;
        }

        return true;
    }

    /**
     * Paints the start of a group test.
     *
     * @param string $test_name     Name of test or other label.
     * @param int $size         Number of test cases starting.
     */
    public function paintGroupStart($test_name, $size)
    {
    }

    /**
     * Paints the end of a group test.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintGroupEnd($test_name)
    {
    }

    /**
     * Paints the start of a test case.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintCaseStart($test_name)
    {
    }

    /**
     * Paints the end of a test case.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintCaseEnd($test_name)
    {
    }

    /**
     * Paints the start of a test method.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintMethodStart($test_name)
    {
    }

    /**
     * Paints the end of a test method.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintMethodEnd($test_name)
    {
    }

    /**
     * Increments the pass count.
     *
     * @param string $message        Message is ignored.
     */
    public function paintPass($message)
    {
        $this->passes++;
    }

    /**
     * Increments the fail count.
     *
     * @param string $message        Message is ignored.
     */
    public function paintFail($message)
    {
        $this->fails++;
    }

    /**
     * Deals with PHP 4 throwing an error.
     *
     * @param string $message    Text of error formatted by the test case.
     */
    public function paintError($message)
    {
        $this->exceptions++;
    }

    /**
     * Deals with PHP 5 throwing an exception.
     *
     * @param Exception $exception    The actual exception thrown.
     */
    public function paintException($exception)
    {
        $this->exceptions++;
    }

    /**
     * Prints the message for skipping tests.
     *
     * @param string $message    Text of skip condition.
     */
    public function paintSkip($message)
    {
    }

    /**
     * Accessor for the number of passes so far.
     *
     * @return int       Number of passes.
     */
    public function getPassCount()
    {
        return $this->passes;
    }

    /**
     * Accessor for the number of fails so far.
     *
     * @return int       Number of fails.
     */
    public function getFailCount()
    {
        return $this->fails;
    }

    /**
     * Accessor for the number of untrapped errors so far.
     *
     * @return int       Number of exceptions.
     */
    public function getExceptionCount()
    {
        return $this->exceptions;
    }

    /**
     * Paints a simple supplementary message.
     *
     * @param string $message        Text to display.
     */
    public function paintMessage($message)
    {
    }

    /**
     * Paints a formatted ASCII message such as a privateiable dump.
     *
     * @param string $message        Text to display.
     */
    public function paintFormattedMessage($message)
    {
    }

    /**
     * By default just ignores user generated events.
     *
     * @param string $type        Event type as text.
     * @param mixed $payload      Message or object.
     */
    public function paintSignal($type, $payload)
    {
    }
}

/**
 * Recipient of generated test messages that can display page footers and headers.
 * Also keeps track of the test nesting.
 * This is the main base class on which to build the finished test (page based) displays.
 */
class SimpleReporter extends SimpleScorer
{
    private $test_stack;
    private $size;
    private $progress;

    /**
     * Starts the display with no results in.
     */
    public function __construct()
    {
        parent::__construct();
        $this->test_stack = array();
        $this->size       = null;
        $this->progress   = 0;
    }

    /**
     * Gets the formatter for small generic data items.
     *
     * @return SimpleDumper          Formatter.
     */
    public function getDumper()
    {
        return new SimpleDumper();
    }

    /**
     * Paints the start of a group test.
     * Will also paint the page header and footer if this is the first test.
     * Will stash the size if the first start.
     *
     * @param string $test_name   Name of test that is starting.
     * @param int $size       Number of test cases starting.
     */
    public function paintGroupStart($test_name, $size)
    {
        if (! isset($this->size)) {
            $this->size = $size;
        }
        if (count($this->test_stack) == 0) {
            $this->paintHeader($test_name);
        }
        $this->test_stack[] = $test_name;
    }

    /**
     * Paints the end of a group test.
     * Will paint the page footer if the stack of tests has unwound.
     *
     * @param string $test_name   Name of test that is ending.
     * @param int $progress   Number of test cases ending.
     */
    public function paintGroupEnd($test_name)
    {
        array_pop($this->test_stack);
        if (count($this->test_stack) == 0) {
            $this->paintFooter($test_name);
        }
    }

    /**
     * Paints the start of a test case.
     * Will also paint the page header and footer if this is the first test.
     * Will stash the size if the first start.
     *
     * @param string $test_name   Name of test that is starting.
     */
    public function paintCaseStart($test_name)
    {
        if (! isset($this->size)) {
            $this->size = 1;
        }
        if (count($this->test_stack) == 0) {
            $this->paintHeader($test_name);
        }
        $this->test_stack[] = $test_name;
    }

    /**
     * Paints the end of a test case.
     * Will paint the page footer if the stack of tests has unwound.
     *
     * @param string $test_name   Name of test that is ending.
     */
    public function paintCaseEnd($test_name)
    {
        $this->progress++;
        array_pop($this->test_stack);
        if (count($this->test_stack) == 0) {
            $this->paintFooter($test_name);
        }
    }

    /**
     * Paints the start of a test method.
     *
     * @param string $test_name   Name of test that is starting.
     */
    public function paintMethodStart($test_name)
    {
        $this->test_stack[] = $test_name;
    }

    /**
     * Paints the end of a test method.
     * Will paint the page footer if the stack of tests has unwound.
     *
     * @param string $test_name   Name of test that is ending.
     */
    public function paintMethodEnd($test_name)
    {
        array_pop($this->test_stack);
    }

    /**
     * Paints the test document header.
     *
     * @param string $test_name     First test top level to start.
     *
     * @abstract
     */
    public function paintHeader($test_name)
    {
    }

    /**
     * Paints the test document footer.
     *
     * @param string $test_name        The top level test.
     *
     * @abstract
     */
    public function paintFooter($test_name)
    {
    }

    /**
     * Accessor for internal test stack.
     * For subclasses that need to see the whole test history for display purposes.
     *
     * @return array     List of methods in nesting order.
     */
    public function getTestList()
    {
        return $this->test_stack;
    }

    /**
     * Accessor for total test size in number of test cases.
     * Null until the first test is started.
     *
     * @return int   Total number of cases at start.
     */
    public function getTestCaseCount()
    {
        return $this->size;
    }

    /**
     * Accessor for the number of test cases completed so far.
     *
     * @return int   Number of ended cases.
     */
    public function getTestCaseProgress()
    {
        return $this->progress;
    }

    /**
     * Static check for running in the comand line.
     *
     * @return bool        True if CLI.
     */
    public static function inCli()
    {
        return PHP_SAPI === 'cli';
    }
}

/**
 * For modifying the behaviour of the visual reporters.
 */
class SimpleReporterDecorator
{
    protected $reporter;

    /**
     * Mediates between the reporter and the test case.
     *
     * @param SimpleScorer $reporter       Reporter to receive events.
     */
    public function __construct($reporter)
    {
        $this->reporter = $reporter;
    }

    /**
     * Signals that the next evaluation will be a dry run.
     * That is, the structure events will be recorded, but no tests will be run.
     *
     * @param bool $is_dry        Dry run if true.
     */
    public function makeDry($is_dry = true)
    {
        $this->reporter->makeDry($is_dry);
    }

    /**
     * Accessor for current status.
     * Will be false if there have been any failures or exceptions.
     * Used for command line tools.
     *
     * @return bool        True if no failures.
     */
    public function getStatus()
    {
        return $this->reporter->getStatus();
    }

    /**
     * The nesting of the test cases so far. Not all reporters have this facility.
     *
     * @return array        Test list if accessible.
     */
    public function getTestList()
    {
        if (method_exists($this->reporter, 'getTestList')) {
            return $this->reporter->getTestList();
        } else {
            return array();
        }
    }

    /**
     * The reporter has a veto on what should be run.
     *
     * @param string $test_case_name  Name of test case.
     * @param string $method          Name of test method.
     *
     * @return bool                True if test should be run.
     */
    public function shouldInvoke($test_case_name, $method)
    {
        return $this->reporter->shouldInvoke($test_case_name, $method);
    }

    /**
     * Can wrap the invoker in preparation for running a test.
     *
     * @param SimpleInvoker $invoker   Individual test runner.
     *
     * @return SimpleInvoker           Wrapped test runner.
     */
    public function createInvoker($invoker)
    {
        return $this->reporter->createInvoker($invoker);
    }

    /**
     * Gets the formatter for privateiables and other small generic data items.
     *
     * @return SimpleDumper          Formatter.
     */
    public function getDumper()
    {
        return $this->reporter->getDumper();
    }

    /**
     * Paints the start of a group test.
     *
     * @param string $test_name     Name of test or other label.
     * @param int $size         Number of test cases starting.
     */
    public function paintGroupStart($test_name, $size)
    {
        $this->reporter->paintGroupStart($test_name, $size);
    }

    /**
     * Paints the end of a group test.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintGroupEnd($test_name)
    {
        $this->reporter->paintGroupEnd($test_name);
    }

    /**
     * Paints the start of a test case.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintCaseStart($test_name)
    {
        $this->reporter->paintCaseStart($test_name);
    }

    /**
     * Paints the end of a test case.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintCaseEnd($test_name)
    {
        $this->reporter->paintCaseEnd($test_name);
    }

    /**
     * Paints the start of a test method.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintMethodStart($test_name)
    {
        $this->reporter->paintMethodStart($test_name);
    }

    /**
     * Paints the end of a test method.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintMethodEnd($test_name)
    {
        $this->reporter->paintMethodEnd($test_name);
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message        Message is ignored.
     */
    public function paintPass($message)
    {
        $this->reporter->paintPass($message);
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message        Message is ignored.
     */
    public function paintFail($message)
    {
        $this->reporter->paintFail($message);
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message    Text of error formatted by the test case.
     */
    public function paintError($message)
    {
        $this->reporter->paintError($message);
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param Exception $exception        Exception to show.
     */
    public function paintException($exception)
    {
        $this->reporter->paintException($exception);
    }

    /**
     * Prints the message for skipping tests.
     *
     * @param string $message    Text of skip condition.
     */
    public function paintSkip($message)
    {
        $this->reporter->paintSkip($message);
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message        Text to display.
     */
    public function paintMessage($message)
    {
        $this->reporter->paintMessage($message);
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message        Text to display.
     */
    public function paintFormattedMessage($message)
    {
        $this->reporter->paintFormattedMessage($message);
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $type        Event type as text.
     * @param mixed $payload      Message or object.
     *
     * @return bool     Should return false, if this type of signal should fail the test suite.
     */
    public function paintSignal($type, $payload)
    {
        $this->reporter->paintSignal($type, $payload);
    }
}

/**
 * For sending messages to multiple reporters at the same time.
 */
class MultipleReporter
{
    private $reporters = array();

    /**
     * Adds a reporter to the subscriber list.
     *
     * @param SimpleScorer $reporter     Reporter to receive events.
     */
    public function attachReporter($reporter)
    {
        $this->reporters[] = $reporter;
    }

    /**
     * Signals that the next evaluation will be a dry run.
     * That is, the structure events will be recorded, but no tests will be run.
     *
     * @param bool $is_dry        Dry run if true.
     */
    public function makeDry($is_dry = true)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->makeDry($is_dry);
        }
    }

    /**
     * Accessor for current status.
     * Will be false if there have been any failures or exceptions.
     * If any reporter reports a failure, the whole suite fails.
     *
     * @return bool        True if no failures.
     */
    public function getStatus()
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            if (! $this->reporters[$i]->getStatus()) {
                return false;
            }
        }

        return true;
    }

    /**
     * The reporter has a veto on what should be run.
     * It requires all reporters to want to run the method.
     *
     * @param string $test_case_name  name of test case.
     * @param string $method          Name of test method.
     */
    public function shouldInvoke($test_case_name, $method)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            if (! $this->reporters[$i]->shouldInvoke($test_case_name, $method)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Every reporter gets a chance to wrap the invoker.
     *
     * @param SimpleInvoker $invoker   Individual test runner.
     *
     * @return SimpleInvoker           Wrapped test runner.
     */
    public function createInvoker($invoker)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $invoker = $this->reporters[$i]->createInvoker($invoker);
        }

        return $invoker;
    }

    /**
     * Paints the start of a group test.
     *
     * @param string $test_name     Name of test or other label.
     * @param int $size         Number of test cases starting.
     */
    public function paintGroupStart($test_name, $size)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintGroupStart($test_name, $size);
        }
    }

    /**
     * Paints the end of a group test.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintGroupEnd($test_name)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintGroupEnd($test_name);
        }
    }

    /**
     * Paints the start of a test case.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintCaseStart($test_name)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintCaseStart($test_name);
        }
    }

    /**
     * Paints the end of a test case.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintCaseEnd($test_name)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintCaseEnd($test_name);
        }
    }

    /**
     * Paints the start of a test method.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintMethodStart($test_name)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintMethodStart($test_name);
        }
    }

    /**
     * Paints the end of a test method.
     *
     * @param string $test_name     Name of test or other label.
     */
    public function paintMethodEnd($test_name)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintMethodEnd($test_name);
        }
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message        Message is ignored.
     */
    public function paintPass($message)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintPass($message);
        }
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message        Message is ignored.
     */
    public function paintFail($message)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintFail($message);
        }
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message    Text of error formatted by the test case.
     */
    public function paintError($message)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintError($message);
        }
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param Exception $exception    Exception to display.
     */
    public function paintException($exception)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintException($exception);
        }
    }

    /**
     * Prints the message for skipping tests.
     *
     * @param string $message    Text of skip condition.
     */
    public function paintSkip($message)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintSkip($message);
        }
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message        Text to display.
     */
    public function paintMessage($message)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintMessage($message);
        }
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $message        Text to display.
     */
    public function paintFormattedMessage($message)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintFormattedMessage($message);
        }
    }

    /**
     * Chains to the wrapped reporter.
     *
     * @param string $type        Event type as text.
     * @param mixed $payload      Message or object.
     *
     * @return bool            Should return false if this type of signal should fail the test
     * suite.
     */
    public function paintSignal($type, $payload)
    {
        $numberOfReporters = count($this->reporters);
        for ($i = 0; $i < $numberOfReporters; $i++) {
            $this->reporters[$i]->paintSignal($type, $payload);
        }
    }

    /**
     * Gets the formatter for privateiables and other small generic data items.
     *
     * @return SimpleDumper          Formatter.
     */
    public function getDumper()
    {
        return new SimpleDumper();
    }
}
