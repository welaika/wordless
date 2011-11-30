<?php

require_once "wordless_preprocessor.php";

/**
 * CoffeePreprocessor is able to compile Coffeescript files using the `coffee` executable.
 **/
class SprocketsPreprocessor extends WordlessPreprocessor
{
  protected $preferences = array(
    "sprockets.ruby_path" => '/usr/bin/ruby'
  );

  public function supported_extensions() {
    return array("js", "js.coffee");
  }

  public function cache_hash($file_path) {
    $hash = array(parent::cache_hash($file_path));
    $base_path = dirname($file_path);
    $files = $this->folder_tree("*.coffee", 0, dirname($base_path));
    sort($files);
    $contents = array();
    foreach ($files as $file) {
      $hash[] = file_get_contents($file);
    }
    return join($hash);
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

    $this->validate_executable($this->pref("sprockets.ruby_path"));

    // On cache miss, we build the JS file from scratch
    $pb = new ProcessBuilder(array(
      $this->pref("sprockets.ruby_path"),
      Wordless::join_paths(dirname(__FILE__), "sprockets_preprocessor.rb")
    ));

    $pb->add('/Users/steffoz/dev/sites/php/molino_valente/wp-content/themes/valente/assets/javascripts');
    $pb->add('/Users/steffoz/dev/sites/php/molino_valente/wp-content/themes/valente/theme/assets/javascripts');

    $pb->add($file_path);

    $proc = $pb->getProcess();
    $code = $proc->run();

    if ($code != 0) {
      $this->die_with_error($proc->getErrorOutput());
    }

    return $proc->getOutput();
  }

}

