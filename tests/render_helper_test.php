<?php

require_once('simpletest/autorun.php');
require_once('support/mocked_get_template_directory.php');
require_once('../wordless/wordless.php');
require_once('../vendor/phamlp/haml/HamlParser.php');
require_once('../wordless/helpers/render_helper.php');

class MediaHelperTest extends UnitTestCase {
    function test_render_template() {
        ob_start();
        render_template( 'posts/single' );
        $output = ob_get_clean();

        $this->assertPattern(
            '/This is my mocked template!/',
            $output
        );
    }

    function test_render_template_with_locals() {
        ob_start();
        render_template( 'posts/single', array( 'answer' => 42 ) );
        $output = ob_get_clean();

        $this->assertPattern(
            '/42/',
            $output
        );
    }
}
