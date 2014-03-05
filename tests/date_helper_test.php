<?php

require_once('simpletest/autorun.php');
require_once('support/mocked___.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers.php');

class DateHelperTest extends UnitTestCase {

  function test_distance_of_time_in_words() {
    // seconds
    $this->assertEqual(
      'less than 5 seconds',
      distance_of_time_in_words(0, 0, true)
    );

    $this->assertEqual(
      'less than 5 seconds',
      distance_of_time_in_words(3, 0, true)
    );

    $this->assertEqual(
      'less than 10 seconds',
      distance_of_time_in_words(5, 0, true)
    );

    $this->assertEqual(
      'less than 10 seconds',
      distance_of_time_in_words(7, 0, true)
    );

    $this->assertEqual(
      'less than 20 seconds',
      distance_of_time_in_words(10, 0, true)
    );

    $this->assertEqual(
      'less than 20 seconds',
      distance_of_time_in_words(15, 0, true)
    );

    $this->assertEqual(
      'half a minute',
      distance_of_time_in_words(20, 0, true)
    );

    $this->assertEqual(
      'half a minute',
      distance_of_time_in_words(30, 0, true)
    );

    $this->assertEqual(
      'less than a minute',
      distance_of_time_in_words(40, 0, true)
    );

    $this->assertEqual(
      'less than a minute',
      distance_of_time_in_words(50, 0, true)
    );

    // minutes
    $this->assertEqual(
      'less than a minute',
      distance_of_time_in_words(0, 0)
    );

    $this->assertEqual(
      'less than a minute',
      distance_of_time_in_words(40, 0)
    );

    $this->assertEqual(
      'less than a minute',
      distance_of_time_in_words(0, 40)
    );

    $this->assertEqual(
      'less than a minute',
      distance_of_time_in_words(-40, 0)
    );

    $this->assertEqual(
      'less than a minute',
      distance_of_time_in_words(0, -40)
    );

    $this->assertEqual(
      '1 minute',
      distance_of_time_in_words(60, 0)
    );

    $this->assertEqual(
      '1 minute',
      distance_of_time_in_words(90, 0)
    );

    $this->assertEqual(
      '2 minutes',
      distance_of_time_in_words(120, 0)
    );

    $this->assertEqual(
      '20 minutes',
      distance_of_time_in_words(20 * 60, 0)
    );

    $this->assertEqual(
      'about 1 hour',
      distance_of_time_in_words(50 * 60, 0)
    );

    $this->assertEqual(
      'about 2 hours',
      distance_of_time_in_words(100 * 60, 0)
    );

    $this->assertEqual(
      'about 20 hours',
      distance_of_time_in_words(20 * 60 * 60, 0)
    );

    $this->assertEqual(
      'about 21 hours',
      distance_of_time_in_words(20 * 60 * 60 + 30 * 60, 0)
    );

    $this->assertEqual(
      '1 day',
      distance_of_time_in_words(24 * 60 * 60, 0)
    );

    $this->assertEqual(
      '1 day',
      distance_of_time_in_words(40 * 60 * 60, 0)
    );

    $this->assertEqual(
      '2 days',
      distance_of_time_in_words(48 * 60 * 60, 0)
    );

    $this->assertEqual(
      'about 1 month',
      distance_of_time_in_words(30 * 24 * 60 * 60, 0)
    );

    $this->assertEqual(
      '2 months',
      distance_of_time_in_words(60 * 24 * 60 * 60, 0)
    );

    $this->assertEqual(
      'about 1 year',
      distance_of_time_in_words(365 * 24 * 60 * 60, 0)
    );

    $this->assertEqual(
      'over 3 years',
      distance_of_time_in_words(3 * 365 * 24 * 60 * 60, 0)
    );
  }

  function test_time_ago_in_words() {
    $this->assertEqual(
      'over 25 years',
      time_ago_in_words(mktime(0, 0, 0, date("n"), date("j"), date("Y") - 25))
    );
  }

  function test_time_tag() {
    date_default_timezone_set("Europe/Rome");
    $this->assertEqual(
      '<time datetime="1986-11-27T11:30:00+01:00" birthday="true">foobar</time>',
      time_tag(mktime(11,30,0,11,27,1986), "foobar", array("birthday" => "true"))
    );
  }

}

