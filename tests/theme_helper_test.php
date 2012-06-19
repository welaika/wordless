<?php

require_once('simpletest/autorun.php');
require_once('support/mocked_bloginfo.php');
require_once('support/mocked_get_template.php');
require_once('support/mocked_get_template_directory.php');
require_once('support/mocked_get_theme_data.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers.php');

class ThemeHelperTest extends UnitTestCase {
  
  function test_mocked_get_bloginfo_template_url() {
    $this->assertEqual(
      'http://mocked.url/wp-content/themes/mocked_theme',
      get_bloginfo('template_url')
    );
  }

  function test_get_theme_name() {
    $this->assertEqual(
      'mocked_theme',
      get_theme_name()
    );
  }

  function test_get_theme_path() {
    $this->assertEqual(
      '/mocked/file/path/to/mocked_root/mocked_theme',
      get_theme_path()
    );
  }

  function test_get_theme_version() {
    // Only get_theme_data() way is tested
    $this->assertEqual(
      '1.0',
      get_theme_version()
    );
  }

}
