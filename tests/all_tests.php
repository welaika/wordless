<?php

require_once('simpletest/autorun.php');
require_once('support/mocked_bloginfo.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers.php');

class AllTests extends TestSuite {
  function __construct() {
    parent::__construct('All tests');
    $this->addFile('tag_helper_test.php');
    $this->addFile('asset_tag_helper_test.php');
    $this->addFile('date_helper_test.php');
    $this->addFile('text_helper_test.php');
    $this->addFile('theme_helper_test.php');
    $this->addFile('url_helper_test.php');
    $this->addFile('number_helper_test.php');
    $this->addFile('render_helper_test.php');
  }
}
