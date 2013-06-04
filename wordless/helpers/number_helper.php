<?php

/**
* This modules provides methods to translate numbers in specific formats,
* such as percentage, phone numbers, rounding, etc.
*
* @ingroup helperclass
* @todo all the class is not documented yet
*/

class NumberHelper {

  private static $DEFAULT_CURRENCY_VALUES = array(
    'format' => "%u%n",
    'negative_format' => "-%u%n",
    'unit' => "$",
    'separator' => ".",
    'delimiter' => ",",
    'precision' => 2,
    'significant' => false,
    'strip_insignificant_zeros' => false
  );

  private function array_delete(&$array, $key) {
    if (array_key_exists($key, $array)) {
      $result = $array[$key];
      unset($array[$key]);
      return $result;
    } else {
      return NULL;
    }
  }

  /**
  * Translates number to currency 
  *
  * @todo
  *   Loss of doc
  *
  * @ingroup helperfunc
  */
  public function number_to_currency($number, $options = array()) {

    if (isset($options['format']))
      $options['negative_format'] = "-" . $options['format'];

    $options = array_merge(self::$DEFAULT_CURRENCY_VALUES, $options);

    $unit = $this->array_delete($options, 'unit');
    $format = $this->array_delete($options, 'format');

    if ($number < 0) {
      $format = $this->array_delete($options, 'negative_format');
      $number = preg_replace('/^-/', '', $number);
    }

    try {
      $value = number_with_precision($number, array_merge($options, array('raise' => true)));
      return preg_replace(array('/%n/', '/%u/'), array($value, $unit), $format);
    } catch (Exception $e) {
      if (array_key_exists('raise', $options) && $options['raise'] == true) {
        throw $e;
      } else {
        return preg_replace(array('/%n/', '/%u/'), array($number, $unit), $format);
      }
    }
  }

  /**
  * Translates numbers to percantage
  *
  * @todo
  *   Loss of doc
  */
  public function number_to_percentage($number, $options = array()) {
    if (empty($number))
      return;

    $options = array_merge(self::$DEFAULT_CURRENCY_VALUES, $options);

    try {
      $value = number_with_precision($number, array_merge($options, array('raise' => true)));
      return "{$value}%";
    } catch (Exception $e) {
      if (array_key_exists('raise', $options) && $options['raise'] == true) {
        throw $e;
      } else {
        return "{$number}%";
      }
    }
  }

  /**
  * Translates numbers to phone numbers
  *
  * @todo
  *   Loss of doc
  */
  public function number_to_phone($number, $options = array()) {
    if (empty($number))
      return;

    if (is_numeric($number))
      $number = (float) $number;
    elseif (array_key_exists('raise', $options) && $options['raise'] == true)
      throw new InvalidArgumentException('number_to_phone function only accepts numbers. Input was: '.$number);

    $area_code    = $this->array_delete($options, 'area_code');
    $delimiter    = $this->array_delete($options, 'delimiter');
    if (empty($delimiter))
      $delimiter = "-";
    $extension    = $this->array_delete($options, 'extension');
    $country_code = $this->array_delete($options, 'country_code');

    if (!empty($area_code)) {
      $number = preg_replace('/(\d{1,3})(\d{3})(\d{4}$)/', "(\\1) \\2{$delimiter}\\3", $number);
    } else {
      $number = preg_replace('/(\d{0,3})(\d{3})(\d{4})$/', "\\1{$delimiter}\\2{$delimiter}\\3", $number);
      if (!empty($delimiter) && $number[0] == $delimiter)
        $number = substr($number, 1);
    }

    $result = array();
    if (!empty($country_code))
      array_push($result, "+{$country_code}{$delimiter}");
    array_push($result, $number);
    if (!empty($extension))
      array_push($result, " x {$extension}");

    return implode($result);
  }

  /**
  * Formats numbers with given delimiter
  *
  * @todo
  *   Loss of doc
  */
  public function number_with_delimiter($number, $options = array()) {
    if (!is_numeric($number) && array_key_exists('raise', $options) && $options['raise'] == true)
      throw new InvalidArgumentException('number_with_delimiter function only accepts numbers. Input was: '.$number);

    $options = array_merge(self::$DEFAULT_CURRENCY_VALUES, $options);

    $parts = explode(".", (string) $number);
    $parts[0] = preg_replace('/(\d)(?=(\d\d\d)+(?!\d))/', "\\1{$options['delimiter']}", $parts[0]);
    return implode($options['separator'], $parts);
  }

  /**
  * Rounds number to the given decimal precision
  *
  * @todo
  *   Loss of doc
  */
  public function number_with_precision($number, $options = array()) {
    if (is_numeric($number))
      $number = (float) $number;
    elseif (array_key_exists('raise', $options) && $options['raise'] == true)
      throw new InvalidArgumentException('number_with_precision function only accepts numbers. Input was: '.$number);

    $options = array_merge(self::$DEFAULT_CURRENCY_VALUES, $options);

    $precision = $this->array_delete($options, 'precision');
    $significant = $this->array_delete($options, 'significant');
    $strip_insignificant_zeros = $this->array_delete($options, 'strip_insignificant_zeros');

    if ($significant && $precision > 0) {
      if ($number == 0) {
        $digits = 1;
        $rounded_number = 0;
      } else {
        $digits = floor(log10(abs($number)) + 1);
        $rounded_number = ((float) round($number / ((float) pow(10, $digits - $precision)))) * pow(10, $digits - $precision);
        $digits = floor(log10(abs($rounded_number)) + 1);
      }

      $precision -= $digits;
      $precision = $precision > 0 ? $precision : 0; // don't let precision be negative
    } else {
      $rounded_number = (float) round($number, $precision);
    }

    $formatted_number = number_with_delimiter(sprintf("%01.{$precision}f", $rounded_number), $options);

    if ($strip_insignificant_zeros) {
      $escaped_separator = preg_quote($options['separator']);
      $formatted_number = preg_replace("/({$escaped_separator})(\d*[1-9])?0+\z/", '\1\2', $formatted_number);
      return preg_replace("/{$escaped_separator}\z/", '', $formatted_number);
    } else {
      return $formatted_number;
    }
  }

}

Wordless::register_helper("NumberHelper");
