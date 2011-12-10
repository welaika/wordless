<?php

require_once('simpletest/autorun.php');
require_once('support/mocked_bloginfo.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers.php');

class UrlHelperTest extends UnitTestCase {

  function test_asset_url() {
    $this->assertEqual(
      'mocked_stylesheet_directory/assets/coconuts',
      asset_url("coconuts")
    );
  }

  function test_image_url() {
    $this->assertEqual(
      'mocked_stylesheet_directory/assets/images/cat.gif',
      image_url("cat.gif")
    );
  }

  function test_stylesheet_url() {
    $this->assertEqual(
      'mocked_stylesheet_directory/assets/stylesheets/screen.css',
      stylesheet_url("screen.css")
    );
  }

  function test_javascript_url() {
    $this->assertEqual(
      'mocked_stylesheet_directory/assets/javascripts/jquery.js',
      javascript_url("jquery.js")
    );
  }
}
