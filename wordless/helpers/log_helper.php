<?php

class DebugHelper {
  function dump($var) {
    echo "<pre style='font-family: Monaco, monospaced;'>";
    print_r($var);
    echo "</pre>";
  }
}

Wordless::register_helper("DebugHelper");
