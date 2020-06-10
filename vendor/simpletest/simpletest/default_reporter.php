<?php

require_once __DIR__ . '/simpletest.php';
require_once __DIR__ . '/scorer.php';
require_once __DIR__ . '/reporter.php';
require_once __DIR__ . '/xml.php';

/**
 * Parser for command line arguments.
 * Extracts the a specific test to run and engages XML reporting when necessary.
 */
class SimpleCommandLineParser
{
    private $to_property = array(
            'case' => 'case', 'c' => 'case',
            'test' => 'test', 't' => 'test',
    );
    private $case           = '';
    private $test           = '';
    private $xml            = false;
    private $junit          = false;
    private $help           = false;
    private $no_skips       = false;
    private $excludes       = array();
    private $doCodeCoverage = false;

    /**
     * Parses raw command line arguments into object properties.
     *
     * @param string $arguments        Raw commend line arguments.
     */
    public function __construct($arguments)
    {
        if (! is_array($arguments)) {
            return;
        }
        foreach ($arguments as $i => $argument) {
            if (preg_match('/^--?(test|case|t|c)=(.+)$/', $argument, $matches)) {
                $property        = $this->to_property[$matches[1]];
                $this->$property = $matches[2];
            } elseif (preg_match('/^--?(test|case|t|c)$/', $argument, $matches)) {
                $property = $this->to_property[$matches[1]];
                if (isset($arguments[$i + 1])) {
                    $this->$property = $arguments[$i + 1];
                }
            } elseif (preg_match('/^--?(cx)=(.+)$/', $argument, $matches)) {
//                 $property = $this->to_property[$matches[1]];
                $this->excludes[] = $matches[2];
            } elseif (preg_match('/^--?(cx)$/', $argument, $matches)) {
//                 $property = $this->to_property[$matches[1]];
                if (isset($arguments[$i + 1])) {
                    $this->excludes[] = $arguments[$i + 1];
                }
            } elseif (preg_match('/^--?(xml|x)$/', $argument)) {
                $this->xml = true;
            } elseif (preg_match('/^--?(junit|j)$/', $argument)) {
                $this->junit = true;
            } elseif (preg_match('/^--?(codecoverage|cc)$/', $argument)) {
                $this->doCodeCoverage = true;
            } elseif (preg_match('/^--?(no-skip|no-skips|s)$/', $argument)) {
                $this->no_skips = true;
            } elseif (preg_match('/^--?(help|h)$/', $argument)) {
                $this->help = true;
            }
        }
    }

    /**
     * Run only this test.
     *
     * @return string        Test name to run.
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * Run only this test suite.
     *
     * @return string        Test class name to run.
     */
    public function getTestCase()
    {
        return $this->case;
    }

    /**
     * Output should be XML or not.
     *
     * @return bool        True if XML desired.
     */
    public function isXml()
    {
        return $this->xml;
    }

    /**
     * Output should be JUnit or not.
     *
     * @return boolean True if JUnit desired.
     */
    public function isJUnit()
    {
        return $this->junit;
    }

    /**
     *    Should code coverage be run or not.
     *    @return boolean        True if code coverage should be run.
     */
    public function doCodeCoverage()
    {
        return $this->doCodeCoverage;
    }

    /**
     *    Array of excluded folders.
     *    @return array        Array of strings to exclude from code coverage.
     */
    public function getExcludes()
    {
        return $this->excludes;
    }

    /**
     * Output should suppress skip messages.
     *
     * @return bool        True for no skips.
     */
    public function noSkips()
    {
        return $this->no_skips;
    }

    /**
     * Output should be a help message. Disabled during XML mode.
     *
     * @return bool        True if help message desired.
     */
    public function help()
    {
        return $this->help && ! ($this->xml || $this->junit);
    }

    /**
     * Returns plain-text help message for command line runner.
     *
     * @return string         String help message
     */
    public function getHelpText()
    {
        return <<<HELP
SimpleTest command line default reporter (autorun)
Usage: php <test_file> [args...]

    -c <class>      Run only the test-case <class>
    -t <method>     Run only the test method <method>
    -s              Suppress skip messages
    -x              Return test results in XML
    -j              Return test results in JUnit format
    -cc             Generate code coverage reports
    -cx             Code coverage exclude folder (may have multiple)
    -h              Display this help message

HELP;
    }
}

/**
 * The default reporter used by SimpleTest's autorun feature.
 * The actual reporters used are dependency injected and can be overridden.
 */
class DefaultReporter extends SimpleReporterDecorator
{
    public $doCodeCoverage = false;
    public $excludes = array();

    /**
     * Assembles the appropriate reporter for the environment.
     */
    public function __construct()
    {
        if (SimpleReporter::inCli()) {
            $parser     = new SimpleCommandLineParser($_SERVER['argv']);
            $this->doCodeCoverage = $parser->doCodeCoverage();
            $this->excludes = $parser->getExcludes();
            if ($parser->isXml()) {
                $interfaces = array('XmlReporter');
            } else if ($parser->isJUnit()) {
               $interfaces = array('JUnitXmlReporter');
            } else {
               $interfaces = array('TextReporter');
            }
            if ($parser->help()) {
                echo $parser->getHelpText();
                exit(1);
            }

            $reporter = new SelectiveReporter(
                SimpleTest::preferred($interfaces), $parser->getTestCase(), $parser->getTest()
            );

            if ($parser->noSkips()) {
                $reporter = new NoSkipsReporter($reporter);
            }
        } else {
            $reporter = new SelectiveReporter(
                    SimpleTest::preferred('HtmlReporter'), @$_GET['c'], @$_GET['t']);
            if (@$_GET['skips'] === 'no' || @$_GET['show-skips'] === 'no') {
                $reporter = new NoSkipsReporter($reporter);
            }
        }
        parent::__construct($reporter);
    }
}
