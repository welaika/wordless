<?php

require_once '../detached.php';
require_once '../reporter.php';

// The following URL will depend on your own installation.
$command = 'php ' . __DIR__ . '/visual_test.php xml';

$test = new TestSuite('Remote tests');
$test->add(new DetachedTestCase($command));
if (SimpleReporter::inCli()) {
    exit($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());
