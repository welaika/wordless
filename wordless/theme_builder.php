<?php

/**
 * WordlessThemeBuilder
 **/
class WordlessThemeBuilder {

  function __construct($theme_name, $theme_dir, $chmod_set) {
    $this->theme_dir = Wordless::join_paths(dirname(get_template_directory()), $theme_dir);
    $this->theme_name = $theme_name;
    $this->chmod_set = $chmod_set;
  }

  public function build() {
    $source_path = Wordless::join_paths(dirname(__FILE__), "theme_builder", "vanilla_theme");
    $this->copy($source_path, $this->theme_dir);
  }

  public function set_as_current_theme() {
    update_option('template', basename($this->theme_dir));
    update_option('stylesheet', basename($this->theme_dir));
    update_option('current_theme', $this->theme_name);
  }

  private function copy($src, $dst) {
    $dir = opendir($src);
    $this->make_directory($dst);
    while(false !== ($file = readdir($dir))) {
      if (($file != '.') && ($file != '..')) {
        if (is_dir($src . '/' . $file)) {
          $this->copy($src . '/' . $file,$dst . '/' . $file);
        } else {
          $source_content = file_get_contents($src . '/' . $file);
          $source_content = str_replace("%THEME_NAME%", $this->theme_name, $source_content);
          file_put_contents($dst . '/' . $file, $source_content);
          chmod($dst . '/' . $file, $this->chmod_set);
        }
      }
    }
    closedir($dir);
  }

  private function make_directory($path) {
    if (!file_exists($path)) {
      mkdir($path, 0775);
      chmod($path, 0775);
    }
  }

}
