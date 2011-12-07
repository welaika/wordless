<?php

require_once "wordless_preprocessor.php";

/**
 * Compile Coffeescript files using the Sprockets gem.
 *
 * SprocketsPreprocessor relies on some preferences to work:
 * - sprockets.ruby_path (defaults to "/usr/bin/ruby"): the path to the Ruby executable
 *
 * You can specify different values for this preferences using the Wordless::set_preference() method.
 *
 * @copyright welaika &copy; 2011 - MIT License
 * @see WordlessPreprocessor
 */
class SprocketsPreprocessor extends WordlessPreprocessor {

  public function __construct() {
    parent::__construct();
    $this->set_preference_default_value("sprockets.ruby_path", '/usr/bin/ruby');
  }

  /**
   * Overrides WordlessPreprocessor::asset_hash()
   * @attention This is raw code. Right now all we do is find all the *.coffee files, concat
   * them togheter and generate an hash. We should find exacty the coffee files required by
   * $file_path asset file.
   */
  protected function asset_hash($file_path) {
    $hash = array(parent::asset_hash($file_path));
    $base_path = dirname($file_path);
    $files = $this->folder_tree(dirname($base_path), "*.coffee");
    sort($files);
    $contents = array();
    foreach ($files as $file) {
      $hash[] = file_get_contents($file);
    }
    return md5(join($hash));
  }

  /**
   * Overrides WordlessPreprocessor::comment_line()
   */
  protected function comment_line($line) {
    return "/* $line */\n";
  }

  /**
   * Overrides WordlessPreprocessor::content_type()
   */
  protected function content_type() {
    return "text/javascript";
  }

  /**
   * Overrides WordlessPreprocessor::die_with_error()
   */
  protected function die_with_error($description) {
    $description = preg_replace('/\n/', '\n', addslashes($description));
    echo sprintf("alert('%s');", $description);
    die();
  }

  /**
   * Overrides WordlessPreprocessor::process_file()
   */
  protected function process_file($file_path, $result_path, $temp_path) {

    $this->validate_executable_or_die($this->preference("sprockets.ruby_path"));

    // On cache miss, we build the JS file from scratch
    $pb = new ProcessBuilder(array(
      $this->preference("sprockets.ruby_path"),
      Wordless::join_paths(dirname(__FILE__), "sprockets_preprocessor.rb")
    ));

    // Fix for MAMP environments, see http://goo.gl/S5KFe for details
    $pb->setEnv("DYLD_LIBRARY_PATH", "");

    $pb->add(Wordless::theme_static_javascripts_path());
    $pb->add(Wordless::theme_javascripts_path());

    $pb->add($file_path);

    $proc = $pb->getProcess();
    $code = $proc->run();

    if ($code != 0) {
      $this->die_with_error($proc->getErrorOutput());
    }

    return $proc->getOutput();
  }

  /**
   * Overrides WordlessPreprocessor::supported_extensions()
   */
  public function supported_extensions() {
    return array("js", "js.coffee");
  }

 /**
  * Overrides WordlessPreprocessor::to_extension()
  */
  public function to_extension() {
    return "js";
  }

}
