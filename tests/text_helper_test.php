<?php

require_once('simpletest/autorun.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers.php');

class TextHelperTest extends UnitTestCase {

  function test_cycle_class() {
    $cycle = new Cycle(array("one", 2, "3"));

    $this->assertEqual("one", $cycle->value());
    $this->assertEqual(2, $cycle->value());
    $this->assertEqual("3", $cycle->value());
    $this->assertEqual("one", $cycle->value());

    $cycle->reset();

    $this->assertEqual("one", $cycle->value());
    $this->assertEqual(2, $cycle->value());
    $this->assertEqual("3", $cycle->value());
  }

  function test_cycle() {
    $this->assertEqual("one", cycle("one", 2, "3"));
    $this->assertEqual(2, cycle("one", 2, "3"));
    $this->assertEqual("3", cycle("one", 2, "3"));
    $this->assertEqual("one", cycle("one", 2, "3"));
    $this->assertEqual(2, cycle("one", 2, "3"));
    $this->assertEqual("3", cycle("one", 2, "3"));
  }

  function test_cycle_resets_with_new_values() {
    $this->assertEqual("even", cycle("even", "odd"));
    $this->assertEqual("odd", cycle("even", "odd"));
    $this->assertEqual("even", cycle("even", "odd"));

    $this->assertEqual("one", cycle("one", 2, "3"));
    $this->assertEqual(2, cycle("one", 2, "3"));
    $this->assertEqual("3", cycle("one", 2, "3"));
    $this->assertEqual("one", cycle("one", 2, "3"));
  }

  function test_cycle_with_names() {
    $this->assertEqual("one", cycle("one", "two", "three", array("name" => "numbers")));
    $this->assertEqual("red", cycle("red", "green", "yellow", array("name" => "colors")));
    $this->assertEqual("two", cycle("one", "two", "three", array("name" => "numbers")));
    $this->assertEqual("green", cycle("red", "green", "yellow", array("name" => "colors")));
    $this->assertEqual("three", cycle("one", "two", "three", array("name" => "numbers")));
    $this->assertEqual("yellow", cycle("red", "green", "yellow", array("name" => "colors")));
  }

  function test_reset_cycle() {
    $this->assertEqual("red", cycle("red", "green", "yellow"));
    $this->assertEqual("green", cycle("red", "green", "yellow"));
    reset_cycle();
    $this->assertEqual("red", cycle("red", "green", "yellow"));
  }

  function test_reset_named_cycle() {
    $this->assertEqual("red", cycle("red", "green", "yellow", array("name" => "numbers")));
    $this->assertEqual("green", cycle("red", "green", "yellow", array("name" => "numbers")));
    reset_cycle("numbers");
    $this->assertEqual("red", cycle("red", "green", "yellow", array("name" => "numbers")));
  }

  function test_truncate() {
    $this->assertEqual("Hello World!", truncate("Hello World!", array("length" => 12)));
    $this->assertEqual("Hello Wor...", truncate("Hello World!!", array("length" => 12)));
  }

  function test_truncate_should_use_default_length_of_30() {
    $str = "This is a string that will go longer then the default truncate length of 30";
    $this->assertEqual(substr($str, 0, -3) + "...", truncate($str));
  }

  function test_truncate_with_options_hash() {
    $this->assertEqual("This is a string that wil[...]", truncate("This is a string that will go longer then the default truncate length of 30", array("omission" => "[...]")));
    $this->assertEqual("Hello W...", truncate("Hello World!", array("length" => 10)));
    $this->assertEqual("Hello[...]", truncate("Hello World!", array("omission" => "[...]", "length" => 10)));
    $this->assertEqual("Hello[...]", truncate("Hello Big World!", array("omission" => "[...]", "length" => 13, "separator" => ' ')));
    $this->assertEqual("Hello Big[...]", truncate("Hello Big World!", array("omission" => "[...]", "length" => 14, "separator" => ' ')));
    $this->assertEqual("Hello Big[...]", truncate("Hello Big World!", array("omission" => "[...]", "length" => 15, "separator" => ' ')));
  }

  function test_truncate_word_count() {
    $this->assertEqual("Hello Big World! I'm[...]", truncate("Hello Big World! I'm very happy to be here.", array("omission" => "[...]", "length" => 4, "separator" => ' ', "word_count" => TRUE)));
     $this->assertEqual("Hello Big World! I'm[...]", truncate("<strong>Hello Big World!</strong> I'm very happy to be here.", array("omission" => "[...]", "length" => 4, "separator" => ' ', "word_count" => TRUE)));
     $this->assertEqual("<strong>Hello Big World!</strong> I'm[...]", truncate("<strong>Hello Big World!</strong> I'm very happy to be here.", array("omission" => "[...]", "length" => 4, "separator" => ' ', "word_count" => TRUE, "html" => TRUE)));
  }

  function test_truncate_allowed_tags() {
    $this->assertEqual("<a><strong>Hello Big World!</strong></a> I'm[...]", truncate("<a><strong>Hello Big World!</a> I'm very</strong> happy to be here.", array("omission" => "[...]", "length" => 4, "separator" => ' ', "word_count" => TRUE, 'html' => TRUE, 'allowed_tags' => 'all')));
    $this->assertEqual("<a>Hello Big World!</a> I'm[...]", truncate("<a><strong>Hello Big World!</a> I'm very</strong> happy to be here.", array("omission" => "[...]", "length" => 4, "separator" => ' ', "word_count" => TRUE, 'html' => TRUE, 'allowed_tags' => array('a','img'))));
  }

  function test_active_if() {
    $this->assertEqual("active", active_if(true));
    $this->assertEqual("inactive", active_if(false));

    $this->assertEqual("selected", active_if(true, "selected", "unselected"));
    $this->assertEqual("unselected", active_if(false, "selected", "unselected"));
  }

  function test_capitalize() {
    $this->assertEqual("Selected", capitalize("selected"));
    $this->assertEqual("One two", capitalize("ONE TWO"));
    $this->assertEqual("Three four", capitalize("three four"));
  }

  function test_is_valid_url() {
    $this->assertEqual(false, is_valid_url("http:///example.unknown")); 
    $this->assertEqual(false, is_valid_url("http://:80")); 
    $this->assertEqual(false, is_valid_url("http://user@:80")); 
    $this->assertEqual(false, is_valid_url("example.unknown"));
    $this->assertEqual(true, is_valid_url("http://example.unknown"));
    $this->assertEqual(true, is_valid_url("http://example.unknown/2013/01/07/something"));
    $this->assertEqual(true, is_valid_url("http://example.unknown/2013/01/07/something.html"));
    $this->assertEqual(true, is_valid_url("http://user:password@example.unknown"));
    $this->assertEqual(true, is_valid_url("http://example.unknown:80"));
    $this->assertEqual(true, is_valid_url("http://user:password@example.unknown:80"));
    $this->assertEqual(true, is_valid_url("http://example.unknown/$-_.+!*'(),{}|\\^~[]`<>#%\";/?:@&=."));
    $this->assertEqual(true, is_valid_url("http://example.unknown/\"><script>alert(document.cookie)</script>"));
  }

  function test_titleize() {
    $this->assertEqual("Selected", titleize("selected"));
    $this->assertEqual("One Two", titleize("ONE TWO"));
    $this->assertEqual("Three Four", titleize("three four"));
  }

}

