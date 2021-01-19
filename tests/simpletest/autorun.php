<?php
/**
 *  Autorunner which runs all tests cases found in a file
 *  that includes this module.
 */

// include simpletest files
require_once __DIR__ . '/unit_tester.php';
require_once __DIR__ . '/mock_objects.php';
require_once __DIR__ . '/collector.php';
require_once __DIR__ . '/default_reporter.php';

$GLOBALS['SIMPLETEST_AUTORUNNER_INITIAL_CLASSES'] = get_declared_classes();
$GLOBALS['SIMPLETEST_AUTORUNNER_INITIAL_PATH']    = getcwd();
register_shutdown_function('simpletest_autorun');

/**
 * Exit handler to run all recent test cases and exit system if in CLI
 */
function simpletest_autorun()
{
    chdir($GLOBALS['SIMPLETEST_AUTORUNNER_INITIAL_PATH']);
    if (tests_have_run()) {
        return true;
    }
    $result = run_local_tests();
    if (SimpleReporter::inCli()) {
        exit($result ? 0 : 1);
    }
}

/**
 * Run all recent test cases if no test has so far been run.
 * Uses the DefaultReporter which can have it's output
 * controlled with SimpleTest::prefer().
 *
 * @return boolean/null false, if there were test failures,
 *                      true, if there were no failures,
 *                      null, if tests are already running
 */
function run_local_tests()
{
    try {
        if (tests_have_run()) {
            return true;
        }
        $candidates = capture_new_classes();
        $loader     = new SimpleFileLoader();

        $suite = $loader->createSuiteFromClasses(
            basename(initial_file()),
            $loader->selectRunnableTests($candidates)
        );

        $reporter = new DefaultReporter();

        if ($reporter->doCodeCoverage) {
            $coverage = new PHP_CodeCoverage;
            $filter   = $coverage->filter();

            foreach ($reporter->excludes as $folderPath) {
                $filter->addDirectoryToBlacklist($folderPath);
            }

            $coverage->start($_SERVER['SCRIPT_FILENAME']);
        }

        $result = $suite->run($reporter);

        if ($reporter->doCodeCoverage) {
            $coverage->stop();

            $writer = new PHP_CodeCoverage_Report_HTML;
            $writer->process($coverage, '/tmp/coverage');
        }

        return $result;
    } catch (Exception $stack_frame_fix) {
        print $stack_frame_fix->getMessage();

        return false;
    }
}

/**
 * Checks the current test context to see if a test has ever been run.
 *
 * @return bool True if tests have run.
 */
function tests_have_run()
{
    $context = SimpleTest::getContext();
    if ($context) {
        return (boolean) $context->getTest();
    }

    return false;
}

/**
 * The first autorun file.
 *
 * @return string Filename of first autorun script.
 */
function initial_file()
{
    static $file = false;
    if (! $file) {
        if (isset($_SERVER, $_SERVER['SCRIPT_FILENAME'])) {
            $file = $_SERVER['SCRIPT_FILENAME'];
        } else {
            $included_files = get_included_files();
            $file           = reset($included_files);
        }
    }

    return $file;
}

/**
 * Every class since the first autorun include.
 * This is safe enough if require_once() is always used.
 *
 * @return array Class names.
 */
function capture_new_classes()
{
    global $SIMPLETEST_AUTORUNNER_INITIAL_CLASSES;

    return array_map('strtolower', array_diff(get_declared_classes(),
                            $SIMPLETEST_AUTORUNNER_INITIAL_CLASSES ?
                            $SIMPLETEST_AUTORUNNER_INITIAL_CLASSES : array()));
}
