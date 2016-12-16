<?php

require_once('simpletest/autorun.php');
require_once('support/mocked_get_template_directory.php');
require_once('../wordless/wordless.php');
require_once('../vendor/phamlp/haml/HamlParser.php');
require_once('../wordless/helpers/render_helper.php');

class RenderHelperTest extends UnitTestCase {
    function test_render_template_haml() {
        ob_start();
        render_template( 'posts/single_haml' );
        $output = ob_get_clean();

        $this->assertPattern(
            '/This is my mocked template!/',
            $output
        );
    }

    function test_render_template_haml_with_locals() {
        ob_start();
        render_template( 'posts/single_haml', array( 'answer' => 42 ) );
        $output = ob_get_clean();

        $this->assertPattern(
            '/42/',
            $output
        );
    }

    function test_render_template_pug() {
        ob_start();
        render_template( 'posts/single_pug' );
        $output = ob_get_clean();

        $this->assertPattern(
            '/This is my mocked template!/',
            $output
        );
    }

    function test_render_template_pug_with_locals() {
        ob_start();
        render_template( 'posts/single_pug', array( 'answer' => 42 ) );
        $output = ob_get_clean();

        $this->assertPattern(
            '/42/',
            $output
        );
    }

}
