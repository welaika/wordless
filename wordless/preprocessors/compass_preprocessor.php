<?php
/**
 * @file
 * Wrapper around the Compass executable to process file directly from PHP.
 */
require_once "wordless_preprocessor.php";

/**
 * Compile Sass files using the `compass` executable.
 * 
 * @copyright welaika &copy; 2011 - MIT License
 * @see WordlessPreprocessor
 */
class CompassPreprocessor extends WordlessPreprocessor {

  /**
   * An array of options used by the preprocessor to compile SASS files.
   * 
   * The array contains:
   * - compass.compass_path: the path to the Compass executable
   * - compass.images_path: the path to the image folder used by the theme
   * - compass.output_style: the output style used to render css files ( check
   *   compass documentation for more details )
   * 
   * @see WordlessPreprocessor::preferences
   */
  protected $preferences = array(
    "compass.compass_path" => '/usr/bin/compass',
    "compass.images_path" => '../images',
    "compass.output_style" => 'compressed'
  );


  /**
   * @see WordlessPreprocessor::cache_hash()
   * @see WordlessPreprocessor::folder_tree()
   * 
   * @todo check docs
   */
  public function cache_hash($file_path) {
    $hash = array(parent::cache_hash($file_path));
    $base_path = dirname($file_path);
    $files = $this->folder_tree("*.sass", 0, dirname($base_path));
    sort($files);
    $contents = array();
    foreach ($files as $file) {
      $hash[] = file_get_contents($file);
    }
    return join($hash);
  }

  /**
   * Create a commented line ( with CSS comment style ).
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
    return "text/css";
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
    echo sprintf("body::before { content: '%s'; font-family: monospace; };", addslashes($description));
    die();
  }

  /**
   * Process a file, executing Compass executable.
   * 
   * Execute the Compass executable, overriding the no-op function inside
   * WordlessPreprocessor.
   * 
   * @see Process::getErrorOutput()
   * @see Process::run()
   * @see ProcessBuilder
   * @see ProcessBuilder::add()
   * @see ProcessBuilder::getProcess()
   * @see WordlessPreprocessor::die_with_error()
   * @see WordlessPreprocessor::pref()
   * @see WordlessPreprocessor::process_file()
   * @see WordlessPreprocessor::validate_executable()
   */
  public function process_file($file_path, $result_path, $temp_path) {

    $this->validate_executable($this->pref("compass.compass_path"));

    // On cache miss, we build the file from scratch
    $pb = new ProcessBuilder(array(
      $this->pref("compass.compass_path"),
      'compile',
      $temp_path
    ));

    $config = array(
      "http_path" => Wordless::theme_url(),
      "images_dir" => "assets/images",
      "css_path" => $temp_path,
      "relative_assets" => false,
      "output_style" => ":" . $this->pref("compass.output_style"),
      "environment" => ":production",
      "sass_path" => dirname($file_path)
    );

    $ruby_config = array();

    foreach ($config as $name => $value) {
      if (strpos($value, ":") === 0) {
        $ruby_config[] = sprintf('%s = %s', $name, $value);
      } else if (is_bool($value)) {
        $ruby_config[] = sprintf('%s = %s', $name, $value ? "true" : "false");
      } else {
        $ruby_config[] = sprintf('%s = "%s"', $name, addcslashes($value, '\\'));
      }
    }

    $config_path = tempnam($temp_path, 'compass_config');
    file_put_contents($config_path, implode("\n", $ruby_config)."\n");

    $pb->add("--config")->add($config_path);

    $output = $temp_path . "/" . basename($file_path, pathinfo($file_path, PATHINFO_EXTENSION)) . 'css';

    $proc = $pb->getProcess();
    $code = $proc->run();

    if (0 < $code) {
      unlink($config_path);
      $this->die_with_error($proc->getErrorOutput());
    }

    unlink($config_path);
    return file_get_contents($output);
  }

  /**
   * Define the file extensions supported by the preprocessor.
   * 
   * @see WordlessPreprocessor::supported_extensions()
   */
  public function supported_extensions() {
    return array("sass", "scss");
  }

  /**
   * Define the extension of processed file.
   * 
   * @todo check documentation
   * @doubt why this is not implemented in WordlessPreprocessor?
   */
  public function to_extension() {
    return "css";
  }

}

