<?php
/**
 * This module provides methods for cycling iteration inside views.
 * 
 * @todo Needs examples.
 *
 * @ingroup helperclass
 */
class Cycle {

  /**
   * Build the class with the specified values.
   * 
   * @param mixed $values
   *   Data through which class will cycle.
   */
  function __construct($values) {
    $this->values = $values;
    $this->index = 0;
  }

  /**
   * Returns values.
   * 
   * @return mixed
   *   The values stored in the $this->values class variable.
   */
  function values() {
    return $this->values;
  }

  /**
   * Reset the cycle index.
   */
  function reset() {
    $this->index = 0;
  }

  /**
   * Returns the current value in iteration.
   * 
   * @return mixed
   *   The current data in the iteration.
   */
  function current_value() {
    return $this->values[$this->index];
  }

  /**
   * Returns the current value and iterate to the next value.
   * 
   * @return mixed
   *   The current data in the iteration.
   */
  function value() {
    $value = $this->current_value();
    $this->next();
    return $value;
  }

  /**
   * Goes to the next value in the iteration.
   */
  function next() {
    $this->index = ($this->index + 1) % count($this->values);
  }
}

/**
 * This module provides methods for text handling.
 * 
 * @todo Needs examples.
 *
 * @ingroup helperclass
 */
class TextHelper {

  private static $cycles = array();

  /**
   * Attempts to pluralize the specified text.
   * 
   * @param string $string
   *   The string to be pluralized.
   * 
   * @return mixed
   *   The current data in the iteration.
   */
  function pluralize($string) {

    $plural = array(
      array('/(quiz)$/i',               "$1zes"  ),
      array('/^(ox)$/i',                "$1en"   ),
      array('/([m|l])ouse$/i',          "$1ice"  ),
      array('/(matr|vert|ind)ix|ex$/i', "$1ices" ),
      array('/(x|ch|ss|sh)$/i',         "$1es"   ),
      array('/([^aeiouy]|qu)y$/i',      "$1ies"  ),
      array('/([^aeiouy]|qu)ies$/i',    "$1y"    ),
      array('/(hive)$/i',               "$1s"    ),
      array('/(?:([^f])fe|([lr])f)$/i', "$1$2ves"),
      array('/sis$/i',                  "ses"    ),
      array('/([ti])um$/i',             "$1a"    ),
      array('/(buffal|tomat)o$/i',      "$1oes"  ),
      array('/(bu)s$/i',                "$1ses"  ),
      array('/(alias|status)$/i',       "$1es"   ),
      array('/(octop|vir)us$/i',        "$1i"    ),
      array('/(ax|test)is$/i',          "$1es"   ),
      array('/s$/i',                    "s"      ),
      array('/$/',                      "s"      )
      );

    $irregular = array(
      array('move',   'moves'   ),
      array('sex',    'sexes'   ),
      array('child',  'children'),
      array('man',    'men'     ),
      array('person', 'people'  )
      );

    $uncountable = array(
      'sheep',
      'fish',
      'series',
      'species',
      'money',
      'rice',
      'information',
      'equipment'
      );

    // save some time in the case that singular and plural are the same
    if (in_array(strtolower($string), $uncountable))
      return $string;

    // check for irregular singular forms
    foreach ($irregular as $noun) {
      if (strtolower($string) == $noun[0])
        return $noun[1];
    }

    // check for matches using regular expressions
    foreach ($plural as $pattern) {
      if (preg_match($pattern[0], $string))
        return preg_replace($pattern[0], $pattern[1], $string);
    }

    return $string;
  }

  /**
   */
  function cycle() {
    $values = func_get_args();
    if (is_array($values[count($values)-1])) {
      $options = array_pop($values);
    }
    $name = isset($options["name"]) ? $options["name"] : "default";

    if (!isset(self::$cycles[$name]) || self::$cycles[$name]->values() != $values) {
      self::$cycles[$name] = new Cycle($values);
    }

    $cycle = self::$cycles[$name];
    return $cycle->value();
  }

  /**
   * Reset the cycle pointer.
   * 
   * @param string $name (optional)
   *   The specified name of the cycle to reset.
   */ 
  function reset_cycle($name = "default") {
    if (isset(self::$cycles[$name])) {
      self::$cycles[$name]->reset();
    }
  }

  /**
   * Truncate the text to the specified length.
   * 
   * @param string $text
   *   The text to be truncated.
   * @param array $options (optional)
   *   An array with options to be passed to the function.
   *   Available options:
   *     - length (default 30): the length at which truncate the text
   *     - omission (default '...'): the suffix string to be added to the 
   *       truncate text
   *     - separator (default FALSE): the separator string used to trim the
   *       argument
   * 
   *  @return string
   *    The truncated text.
   */
  function truncate($text, $options = array()) {
    $options = array_merge(
      array(
        'length' => 30,
        'omission' => '...',
        'separator' => FALSE
      ),
      $options
    );

    $length_with_room_for_omission = $options['length'] - strlen($options['omission']);
    if ($options['separator']) {
      $stop = FALSE;
      for ($i = 0; $i <= min(strlen($text), $length_with_room_for_omission); $i++) {
        if (substr($text, $i, strlen($options['separator'])) == $options['separator']) {
          $stop = $i;
        }
      }
      if ($stop === FALSE) {
        $stop = $length_with_room_for_omission;
      }
    } 
    else {
      $stop = $length_with_room_for_omission;
    }

    if (strlen($text) > $options['length']) {
      return substr($text, 0, $stop) . $options['omission'];
    }
    else {
      return $text;
    }
  }

  function active_if($check, $active = "active", $inactive = "inactive") {
    return $check ? $active : $inactive;
  }

  /**
   * Returns a string with the first character of each word capitalized.
   * 
   * @param string $text
   *   The text to be capitalized.
   * 
   * @return string
   *   The capitalized text.
   */
  function capitalize($text) {
    return ucwords($text);
  }

  /**
   * Capitalizes the first letter of every word.
   * 
   * @param string $text
   *   The text to be capitalized.
   * 
   * @return string
   *   The text with every word capitalized.
   */
  function titleize($text) {
    $words = split(" ", $text);
    $capitalized_words = array();
    foreach ($words as $word) {
      $capitalized_words[] = capitalize($word);
    }
    return join(" ", $capitalized_words);
  }
}

Wordless::register_helper("TextHelper");
