<?php

require_once('simpletest/autorun.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers.php');

class NumberHelperTest extends UnitTestCase {

  function test_number_to_currency_exception() {
    $this->expectException(new InvalidArgumentException('number_with_precision function only accepts numbers. Input was: 123a456'));
    number_with_precision("123a456", array('raise' => true));
  }

  function test_number_to_currency() {
    $this->assertEqual(
      "$1,234,567,890.50",
      number_to_currency(1234567890.50)
    );

    $this->assertEqual(
      "$1,234,567,890.51",
      number_to_currency(1234567890.506)
    );

    $this->assertEqual(
      "$1,234,567,890.506",
      number_to_currency(1234567890.506, array('precision' => 3))
    );

    $this->assertEqual(
      "$123a456",
      number_to_currency("123a456")
    );

    $this->assertEqual(
      "($1,234,567,890.50)",
      number_to_currency(-1234567890.50, array('negative_format' => "(%u%n)"))
    );

    $this->assertEqual(
      "&pound;1234567890,50",
      number_to_currency(1234567890.50, array('unit' => "&pound;", 'separator' => ",", 'delimiter' => ""))
    );

    $this->assertEqual(
      "1234567890,50 &pound;",
      number_to_currency(1234567890.50, array('unit' => "&pound;", 'separator' => ",", 'delimiter' => "", 'format' => "%n %u"))
    );
  }

  function test_number_to_percentage_exception() {
    $this->expectException(new InvalidArgumentException('number_with_precision function only accepts numbers. Input was: 98a'));
    number_with_precision("98a", array('raise' => true));
  }

  function test_number_to_percentage() {
    $this->assertEqual(
      "100.00%",
      number_to_percentage(100)
    );

    $this->assertEqual(
      "98.00%",
      number_to_percentage("98")
    );

    $this->assertEqual(
      "100%",
      number_to_percentage(100, array('precision' => 0))
    );

    $this->assertEqual(
      "1.000,00%",
      number_to_percentage(1000, array('delimiter' => '.', 'separator' => ','))
    );

    $this->assertEqual(
      "302.24399%",
      number_to_percentage(302.24398923423, array('precision' => 5))
    );

    $this->assertEqual(
      "98a%",
      number_to_percentage("98a")
    );
  }

  function test_number_to_phone_exception() {
    $this->expectException(new InvalidArgumentException('number_to_phone function only accepts numbers. Input was: 123a456'));
    number_to_phone("123a456", array('raise' => true));
  }

  function test_number_to_phone() {
    $this->assertEqual(
      "555-1234",
      number_to_phone(5551234)
    );

    $this->assertEqual(
      "555-1234",
      number_to_phone("5551234")
    );

    $this->assertEqual(
      "123-555-1234",
      number_to_phone(1235551234)
    );

    $this->assertEqual(
      "(123) 555-1234",
      number_to_phone(1235551234, array('area_code' => true))
    );

    $this->assertEqual(
      "123 555 1234",
      number_to_phone(1235551234, array('delimiter' => " "))
    );

    $this->assertEqual(
      "(123) 555-1234 x 555",
      number_to_phone(1235551234, array('area_code' => true, 'extension' => 555))
    );

    $this->assertEqual(
      "+1-123-555-1234",
      number_to_phone(1235551234, array('country_code' => 1))
    );

    $this->assertEqual(
      "123a456",
      number_to_phone("123a456")
    );

    $this->assertEqual(
      "+1.123.555.1234 x 1343",
      number_to_phone(1235551234, array('country_code' => 1, 'extension' => 1343, 'delimiter' => "."))
    );
  }

  function test_number_with_delimiter_exception() {
    $this->expectException(new InvalidArgumentException('number_with_delimiter function only accepts numbers. Input was: 132131spam35432'));
    number_with_delimiter("132131spam35432", array('raise' => true));
  }

  function test_number_with_delimiter() {
    $this->assertEqual(
      "12,345,678",
      number_with_delimiter(12345678)
    );

    $this->assertEqual(
      "123,456",
      number_with_delimiter("123456")
    );

    $this->assertEqual(
      "12,345,678.05",
      number_with_delimiter("12345678.05")
    );

    $this->assertEqual(
      "12.345.678",
      number_with_delimiter(12345678, array('delimiter' => "."))
    );

    $this->assertEqual(
      "12,345,678",
      number_with_delimiter(12345678, array('delimiter' => ","))
    );

    $this->assertEqual(
      "12,345,678 05",
      number_with_delimiter(12345678.05, array('separator' => " "))
    );

    $this->assertEqual(
      "112a",
      number_with_delimiter("112a")
    );

    $this->assertEqual(
      "98 765 432,98",
      number_with_delimiter(98765432.98, array('delimiter' => " ", 'separator' => ","))
    );
  }

  function test_number_with_precision_exception() {
    $this->expectException(new InvalidArgumentException('number_with_precision function only accepts numbers. Input was: 132131spam35432'));
    number_with_precision("132131spam35432", array('raise' => true));
  }

  function test_number_with_precision() {
    $this->assertEqual(
      "111.23",
      number_with_precision(111.2345)
    );

    $this->assertEqual(
      "111.23",
      number_with_precision(111.2345, array('precision' => 2))
    );

    $this->assertEqual(
      "13.00000",
      number_with_precision(13, array('precision' => 5))
    );

    $this->assertEqual(
      "389",
      number_with_precision(389.32314, array('precision' => 0))
    );

    $this->assertEqual(
      "110",
      number_with_precision(111.2345, array('significant' => true))
    );

    $this->assertEqual(
      "100",
      number_with_precision(111.2345, array('precision' => 1, 'significant' => true))
    );

    $this->assertEqual(
      "13.000",
      number_with_precision(13, array('precision' => 5, 'significant' => true))
    );

    $this->assertEqual(
      "13",
      number_with_precision(13, array('precision' => 5, 'significant' => true, 'strip_insignificant_zeros' => true))
    );

    $this->assertEqual(
      "389.3",
      number_with_precision(389.32314, array('precision' => 4, 'significant' => true))
    );

    $this->assertEqual(
      "1.111,23",
      number_with_precision(1111.2345, array('precision' => 2, 'separator' => ',', 'delimiter' => '.'))
    );
  }
}

