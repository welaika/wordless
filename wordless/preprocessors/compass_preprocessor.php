<?php

require_once "wordless_preprocessor.php";

/**
 * CompassPreprocessor is able to compile Sass files using the `compass` executable.
 **/
class CompassPreprocessor extends WordlessPreprocessor
{

  protected $preferences = array(
    "compass.compass_path" => '/usr/bin/compass',
    "compass.images_path" => '../images',
    "compass.output_style" => 'compressed'
  );

  public function supported_extensions() {
    return array("sass", "scss");
  }

  public function to_extension() {
    return "css";
  }

  public function content_type() {
    return "text/css";
  }

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

  public function process_file($file_path, $result_path, $temp_path) {

    // On cache miss, we build the JS file from scratch
    $pb = new ProcessBuilder(array(
      $this->pref("compass.compass_path"),
      'compile',
      $temp_path
    ));

    $config = array(
      "http_path" => Wordless::theme_url(),
      "images_dir" => "public/images",
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

  public function comment_line($line) {
    return "/* $line */\n";
  }

  public function die_with_error($description) {
    return sprintf("body::before { content: '%s'; font-family: monospace; };", addslashes($description));
    die();
  }

  private function folder_tree($pattern = '*', $flags = 0, $path = false, $depth = -1, $level = 0) {
    $files = glob($path.$pattern, $flags);
    if (!is_array($files)) {
      $files = array();
    }
    $paths = glob($path.'*', GLOB_ONLYDIR|GLOB_NOSORT);

    if (!empty($paths) && ($level < $depth || $depth == -1)) {
      $level++;
      foreach ($paths as $sub_path) {
        $subfiles = $this->folder_tree($pattern, $flags, $sub_path.DIRECTORY_SEPARATOR, $depth, $level);
        if (is_array($subfiles))
          $files = array_merge($files, $subfiles);
      }
    }

    return $files;
  }
}

