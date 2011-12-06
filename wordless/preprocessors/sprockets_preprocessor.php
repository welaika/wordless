<?php
/**
 * @file
 * Wrapper around the Sprockets Ruby Gem to execute the Coffescript executable
 * and compile coffescripts.
 */
require_once "wordless_preprocessor.php";

/**
 * Compile Coffeescript files using the `coffee` executable.
 * 
 * @copyright welaika &copy; 2011 - MIT License
 * @see WordlessPreprocessor
 **/
class SprocketsPreprocessor extends WordlessPreprocessor {
  
  /**
   * An array of options used by the preprocessor to compile Coffescripts.
   * 
   * The array contains:
   * - sprockets.ruby_path: the path to the Ruby executable
   * 
   * @see WordlessPreprocessor::preferences
   */
  protected $preferences = array(
    "sprockets.ruby_path" => '/usr/bin/ruby'
  );


  /**
   * @see WordlessPreprocessor::cache_hash()
   * @see WordlessPreprocessor::folder_tree()
   * 
   * @todo check documentation
   */
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

  /**
   * Create a commented line ( with Javascript comment style ).
   * 
   * @see WordlessPreprocessor::comment_line()
   */
  public function comment_line($line) {
    return "/* $line */\n";
  }

  /**
   * Define the MIME content type of compiled files.
   * 
   * @see WordlessPreprocessor::content_type()
   */
  public function content_type() {
    return "text/javascript";
  }

  /**
   * Thrown in case of errors to die "nicely" displaying errors.
   * 
   * If error occurred this function is thrown to display errors and stop
   * processing.
   * 
   * @param string $description
   *   The description of the occurred error
   * 
   * @doubt In WordlessPreprocessor there isn't this function but is called... 
   *   Check if it's ok...
   */
  public function die_with_error($description) {
    $description = preg_replace('/\n/', '\n', addslashes($description));
    echo sprintf("alert('%s');", $description);
    die();
  }

  /**
   * Process a file, executing Coffee executable.
   * 
   * Execute the Coffee executable, overriding the no-op function inside
   * WordlessPreprocessor.
   * 
   * @see Process::getErrorOutput()
   * @see Process::getOutput()
   * @see Process::run()
   * @see ProcessBuilder::add()
   * @see ProcessBuilder::getProcess()
   * @see ProcessBuilder::setEnv()
   * @see Wordless::join_paths()
   * @see Wordless::Wordless::theme_javascripts_path()
   * @see Wordless::Wordless::theme_static_javascripts_path()
   * @see WordlessPreprocessor::pref()
   * @see WordlessPreprocessor::process_file()
   * @see WordlessPreprocessor::validate_executable()
   */
  public function process_file($file_path, $result_path, $temp_path) {

    $this->validate_executable($this->pref("sprockets.ruby_path"));

    // On cache miss, we build the JS file from scratch
    $pb = new ProcessBuilder(array(
      $this->pref("sprockets.ruby_path"),
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
   * Define the file extensions supported by the preprocessor.
   * 
   * @see WordlessPreprocessor::supported_extensions()
   */
  public function supported_extensions() {
    return array("js", "js.coffee");
  }
  
  /**
   * Define the extension of processed file.
   * 
   * @see WordlessPreprocessor::to_extension()
   */
  public function to_extension() {
    return "js";
  }

}
