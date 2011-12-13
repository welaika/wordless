<?php

require_once "wordless_preprocessor.php";

/**
 * Compile Sass files using the `compass` executable.
 *
 * CompassPreprocessor relies on some preferences to work:
 * - compass.compass_path (defaults to "/usr/bin/compass"): the path to the Compass executable
 * - compass.output_style (defaults to "compressed"): the output style used to render css files
 *   (check Compass documentation for more details: http://compass-style.org/help/tutorials/configuration-reference/)
 *
 * You can specify different values for this preferences using the Wordless::set_preference() method.
 *
 * @copyright welaika &copy; 2011 - MIT License
 * @see WordlessPreprocessor
 */
class CompassPreprocessor extends WordlessPreprocessor {

  public function __construct() {
    parent::__construct();
    $this->set_preference_default_value("compass.compass_path", "/usr/bin/compass");
    $this->set_preference_default_value("compass.output_style", "compressed");
  }

  /**
   * Overrides WordlessPreprocessor::asset_hash()
   * @attention This is raw code. Right now all we do is find all the *.{sass,scss} files, concat
   * them togheter and generate an hash. We should find exacty the coffee files required by
   * $file_path asset file.
   */
  protected function asset_hash($file_path) {
    $hash = array(parent::asset_hash($file_path));
    $base_path = dirname($file_path);
    $files = $this->folder_tree(dirname($base_path), "*.{sass,scss}", GLOB_BRACE);
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
    return "text/css";
  }

  /**
   * Overrides WordlessPreprocessor::die_with_error()
   */
  protected function die_with_error($description) {
    $description = preg_replace('/\n/', '\n', addslashes($description));
    echo sprintf('body::before { content: "%s"; font-family: monospace; }', $description);
    die();
  }

  /**
   * Process a file, executing Compass executable.
   *
   * Execute the Compass executable, overriding the no-op function inside
   * WordlessPreprocessor.
   */
  protected function process_file($file_path, $result_path, $temp_path) {

    $this->validate_executable_or_die($this->preference("compass.compass_path"));

    // On cache miss, we build the file from scratch
    $pb = new ProcessBuilder(array(
      $this->preference("compass.compass_path"),
      'compile',
      $temp_path
    ));

    $config = array(
      "http_path" => Wordless::theme_url(),
      "http_images_dir" => "assets/images",
      "images_dir" => "../assets/images",
      "http_fonts_dir" => "assets/fonts",
      "fonts_dir" => "../assets/fonts",
      "css_path" => $temp_path,
      "relative_assets" => false,
      "output_style" => ":" . $this->preference("compass.output_style"),
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
   * Overrides WordlessPreprocessor::supported_extensions()
   */
  public function supported_extensions() {
    return array("sass", "scss");
  }


  /**
   * Overrides WordlessPreprocessor::to_extension()
   */
  public function to_extension() {
    return "css";
  }

}

