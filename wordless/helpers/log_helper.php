<?php
/**
 * This module provides methods for handling log and debug output.
 * 
 * @ingroup helperclass
 */
class DebugHelper {

  /**
   * Prints the specified variable inside <pre> tags.
   * 
   * @param string $var
   *   The variable to be printed
   * 
   * @ingroup helperfunc
   */
  function dump($var) {
    echo "<pre style='font-family: Monaco, monospaced;'>";
    print_r($var);
    echo "</pre>";
  }
}

Wordless::register_helper("DebugHelper");
