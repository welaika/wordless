<?php

include __DIR__ . '/lib/bootstrap.php';

extract(get_tests_results($verbose = true));

$sum = $success + $failures;
print $sum
    ? "Success: $success\n" .
      "Failures: $failures\n" .
      "Cover: " . round(100 * $success / $sum) . "%\n"
    : "No test found.\n";
