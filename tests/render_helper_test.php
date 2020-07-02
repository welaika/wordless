<?php

<<<<<<< HEAD
require_once( 'simpletest/autorun.php' );
require_once( 'support/mocked_get_template_directory.php' );
require_once( '../wordless/wordless.php' );
require_once( __DIR__.'/../vendor/phamlp/haml/HamlParser.php' );
require_once( __DIR__.'/../wordless/helpers/render_helper.php' );
require_once( 'support/mocked_apply_filters.php' );

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
=======
require_once('simpletest/autorun.php');
require_once('support/mocked_get_template_directory.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers/render_helper.php');
require_once('../wordless/helpers/pug/wordless_pug_options.php');
require_once('support/mocked_apply_filters.php');

class RenderHelperTest extends UnitTestCase {
    function test_render_template_pug() {
        ob_start();
        render_template( 'posts/single_pug' );
        $output = ob_get_clean();
>>>>>>> 3b6ef66f65cad0c6c2b986aa1dedca78b49e7670

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

	// test_pug_instance_options_with_wp_debug_false()
	// test_pug_instance_options_with_wp_debug_true()
	// these functions are order dependend

<<<<<<< HEAD
	function test_pug_instance_options_with_wp_debug_false() {
		xdebug_break();
		$this->assertEqual(
			array(
				'expressionLanguage' => 'php',
				'extension'          => '.pug',
				'cache'              => Wordless::theme_temp_path(),
				'strict'             => true,
				'debug'              => false,
				'enable_profiler'    => false,
				'error_reporting'    => E_ERROR | E_USER_ERROR,
			),
			WordlessPugOptions::get_options()
		);
	}
=======
    function test_pug_instance_options_with_wp_debug_false() {
        $this->assertEqual(
            array(
                'expressionLanguage' => 'php',
                'extension' => '.pug',
                'cache' => Wordless::theme_temp_path(),
                'strict' => true,
                'debug' => false,
                'enable_profiler' => false,
                'error_reporting' => E_ERROR | E_USER_ERROR
            ),
            WordlessPugOptions::get_options()
        );
    }
>>>>>>> 3b6ef66f65cad0c6c2b986aa1dedca78b49e7670

	function test_pug_instance_options_with_wp_debug_true() {
		define( 'WP_DEBUG', true );

		$this->assertEqual(
			array(
				'expressionLanguage' => 'php',
				'extension'          => '.pug',
				'cache'              => Wordless::theme_temp_path(),
				'strict'             => true,
				'debug'              => true,
				'enable_profiler'    => false,
				'error_reporting'    => E_ERROR | E_USER_ERROR,
			),
			WordlessPugOptions::get_options()
		);
	}
}
