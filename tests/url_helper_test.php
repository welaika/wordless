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

  function test_is_absolute_url() {
    $this->assertTrue(is_absolute_url('http://www.google.com'));
    $this->assertTrue(is_absolute_url('//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js'));
    $this->assertFalse(is_absolute_url('/ajax/libs/jquery/1.4.2/jquery.js'));
    // ftp is not supported
    $this->assertFalse(is_absolute_url('ftp://ajax/libs/jquery/1.4.2/jquery.js'));
  }

  function test_is_root_relative_url() {
    $this->assertTrue(is_root_relative_url('/img/logo.png'));
    $this->assertFalse(is_root_relative_url('//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js'));
    $this->assertFalse(is_root_relative_url('ajax/libs/jquery/1.4.2/jquery.js'));
    $this->assertFalse(is_root_relative_url('http://ajax.googleapis.comajax/libs/jquery/1.4.2/jquery.js'));
  }

  function test_is_relative_url() {
    $this->assertTrue(is_relative_url('img/logo.png'));
    $this->assertFalse(is_relative_url('//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js'));
    $this->assertFalse(is_relative_url('/ajax/libs/jquery/1.4.2/jquery.js'));
    $this->assertFalse(is_relative_url('http://ajax.googleapis.comajax/libs/jquery/1.4.2/jquery.js'));
  }
}
