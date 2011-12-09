<?php

require_once('simpletest/autorun.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers.php');

class TagHelperTest extends UnitTestCase {

  function test_content_tag() {

    $this->assertEqual(
      '<a href="create" class="test">Create</a>',
      content_tag("a", "Create", array('href' => 'create', 'class' => 'test'))
    );

    $this->assertEqual(
      '<textarea><br/></textarea>',
      content_tag("textarea", "<br/>", NULL, false)
    );

    $this->assertEqual(
      '<textarea>&lt;br/&gt;</textarea>',
      content_tag("textarea", "<br/>", NULL, true)
    );

    $this->assertEqual(
      '<br class="break"/>',
      content_tag("br", NULL, array('class' => 'break'))
    );

    $this->assertEqual(
      '<select multiple></select>',
      content_tag("select", "", array('multiple' => true))
    );

    $this->assertEqual(
      '<select multiple></select>',
      content_tag("select", "", array('multiple' => 'multiple'))
    );

    $this->assertEqual(
      '<select multiple></select>',
      content_tag("select", "", array('multiple' => ''))
    );

  }

  function test_option_tag() {

    $this->assertEqual(
      '<option name="id" value="3">weLaika</option>',
      option_tag("weLaika", "id", 3)
    );

    $this->assertEqual(
      '<option name="id" value="3" selected>weLaika</option>',
      option_tag("weLaika", "id", 3, true)
    );

  }

  function test_link_to() {

    $this->assertEqual(
      '<a href="#">Create</a>',
      link_to("Create")
    );

    $this->assertEqual(
      '<a href="create">Create</a>',
      link_to("Create", "create")
    );

    $this->assertEqual(
      '<a href="create" class="test">Create</a>',
      link_to("Create", "create", array('class' => 'test'))
    );

  }


}
