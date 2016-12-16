<?php

require_once('simpletest/autorun.php');
require_once('support/mocked_get_template_directory.php');
require_once('../wordless/wordless.php');

require_once('../wordless/helpers/media_helper.php');

class MediaHelperTest extends UnitTestCase {
    function test_detect_user_agent() {
        $detect = detect_user_agent();
        $detect->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');

        $this->assertTrue(
            $detect->isIphone()
        );
    }
}
