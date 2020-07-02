<?php

require_once( 'simpletest/autorun.php' );
require_once( 'support/mocked_bloginfo.php' );
require_once( 'support/mocked_get_template_directory.php' );
require_once('support/mock_wp_esc_value.php');
require_once ('support/mocked_apply_filters.php');
require_once( '../wordless/wordless.php' );
require_once( __DIR__ . '/../wordless/helpers.php' );

class AllTests extends TestSuite {
	function __construct() {
		parent::__construct( 'All tests' );
	//	$this->addFile( __DIR__.'/tag_helper_test.php' );
		$this->addFile(__DIR__.'/asset_tag_helper_test.php');
		$this->addFile(__DIR__.'/date_helper_test.php');
		$this->addFile(__DIR__.'/text_helper_test.php');
		$this->addFile(__DIR__.'/theme_helper_test.php');
		$this->addFile(__DIR__.'/url_helper_test.php');
	    $this->addFile(__DIR__.'/number_helper_test.php');
	//	$this->addFile(__DIR__.'/render_helper_test.php');
	}
}
