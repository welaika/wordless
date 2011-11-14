<?php

require_once "wordless_preprocessor.php";

/**
 * CoffeePreprocessor is able to compile Coffeescript files using the `coffee` executable.
 **/
class CoffeePreprocessor extends WordlessPreprocessor
{
  protected $preferences = array(
    "coffeescript.nodejs_path" => '/usr/bin/node',
    "coffeescript.coffee_path" => '/usr/bin/coffee',
    "coffeescript.bare" => false
  );

  public function supported_extensions() {
    return array("coffee", "coffeescript");
  }

  public function to_extension() {
    return "js";
  }

  public function content_type() {
    return "text/javascript";
  }

  public function comment_line($line) {
    return "/* $line */\n";
  }

  public function die_with_error($description) {
    echo sprintf("alert('%s');", addslashes($description));
    die();
  }

  public function process_file($file_path, $result_path, $temp_path) {

    // On cache miss, we build the JS file from scratch
    $pb = new ProcessBuilder(array(
      $this->pref("coffeescript.nodejs_path"),
      $this->pref("coffeescript.coffee_path"),
      '-cp'
    ));

    if ($this->pref("coffeescript.bare")) {
      $pb->add('--bare');
    }

    $pb->add($file_path);
    $proc = $pb->getProcess();
    $code = $proc->run();

    if (0 < $code) {
      $this->die_with_error($proc->getErrorOutput());
    }

    return $proc->getOutput();
  }

}
