<?php
/*
Plugin Name: Wordless
Plugin URI: https://github.com/welaika/wordless
Description: A theme framework.
Version: 0.1
Author: weLaika
Author URI: http://welaika.com/
License: GPL2
*/

class Wordless {

  public static function initialize() {
    self::load_i18n();
    self::require_helpers();
    self::require_theme_initializers();
    self::add_assets_rewrite_rules();
  }

  public static function add_assets_rewrite_rules() {
    add_action('init', array('Wordless', 'assets_rewrite_rules'));
    add_action('query_vars', array('Wordless', 'query_vars'));
    add_action('parse_request', array('Wordless', 'parse_request'));
  }

  public static function parse_request(&$wp) {
    if (array_key_exists('wordless_sass_precompile', $wp->query_vars)) {
      require_once 'wordless/sass_compiler.php';
      exit();
    }
  }

  public static function query_vars($query_vars) {
    $query_vars[] = 'wordless_sass_precompile';
    $query_vars[] = 'sass_file_path';
    return $query_vars;
  }

  public static function assets_rewrite_rules() {
    global $wp_rewrite;
    add_rewrite_rule('(.*)\.sass.css$', 'index.php?wordless_sass_precompile=true&sass_file_path=$matches[1]', 'top');
  }

  public static function load_i18n() {
    $locales_path = self::theme_locales_path();
    if (file_exists($locales_path) && is_dir($locales_path)) {
      load_theme_textdomain('we', $locales_path);
    }
  }

  public static function require_helpers() {
    require_once 'wordless/helpers.php';
    $helpers_path = self::theme_helpers_path();
    foreach (glob("$helpers_path/*.php") as $filename) {
      require_once $filename;
    }
  }

  public static function require_theme_initializers() {
    $initializers_path = self::theme_initializers_path();
    foreach (glob("$initializers_path/*.php") as $filename) {
      require_once $filename;
    }
  }

  public static function theme_helpers_path() {
    return self::join_paths(get_template_directory(), 'config/helpers');
  }

  public static function theme_initializers_path() {
    return self::join_paths(get_template_directory(), 'config/initializers');
  }

  public static function theme_locales_path() {
    return self::join_paths(get_template_directory(), 'config/locales');
  }

  public static function theme_views_path() {
    return self::join_paths(get_template_directory(), 'theme/views');
  }

  public static function theme_stylesheets_path() {
    return self::join_paths(get_template_directory(), 'theme/assets/stylesheets');
  }

  public static function theme_temp_path() {
    return self::join_paths(get_template_directory(), 'tmp');
  }

  public static function join_paths() {
    $args = func_get_args();
    $paths = array();

    foreach($args as $arg) {
      $paths = array_merge($paths, (array)$arg);
    }

    foreach($paths as &$path) {
      $path = trim($path, '/');
    }

    if (substr($args[0], 0, 1) == '/') {
      $paths[0] = '/' . $paths[0];
    }

    return join('/', $paths);
  }

}

Wordless::initialize();

