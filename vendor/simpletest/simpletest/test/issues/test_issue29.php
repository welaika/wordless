<?php

require_once __DIR__ . '/../../autorun.php';
require_once __DIR__ . '/../../test_case.php';

/**
 * @link https://github.com/simpletest/simpletest/issues/29
 */
class Issue29 extends UnitTestCase
{
    public function testShouldEscapePercentSignInMessageContainingAnUnescapedURL()
    {
        $this->assertEqual(1,1, 'http://www.domain.com/some%20name.html');
        $this->assertEqual(1,1, 'http://www.domain.com/some%20long%20name.html');
    }
}