<?php

require_once Wordless::join_paths(dirname(__FILE__), "admin.php");
require_once Wordless::join_paths(dirname(__FILE__), "preprocessors.php");

/**
 * Wordless holds all the plugin setup and initialization.
 */
class Wordless {

  private static $preprocessors = array();
  private static $preferences = array();
  private static $helpers = array();

  public static function initialize() {
    if (Wordless::theme_is_wordless_compatible()){
        self::load_i18n();
        self::require_helpers();
        self::require_theme_initializers();
        self::register_activation();
        self::register_preprocessors("SprocketsPreprocessor", "CompassPreprocessor");
        self::register_preprocessor_actions();
    }
    self::load_admin_page();
  }

  public static function load_admin_page() {
    WordlessAdmin::initialize();
  }

  public static function helper($class_name) {
    if (!isset(self::$helpers[$class_name])) {
      self::$helpers[$class_name] = new $class_name();
    }
    return self::$helpers[$class_name];
  }

  public static function register_helper($class_name) {
    foreach (get_class_methods($class_name) as $method) {
      if (!function_exists($method)) {
        $global_function_definition = "function $method() { \$helper = Wordless::helper('$class_name'); \$args = func_get_args(); return call_user_func_array(array(\$helper, '$method'), \$args); }";
        eval($global_function_definition);
      }
    }
  }

  public static function register_preprocessors() {
    foreach (func_get_args() as $preprocessor_class) {
      self::$preprocessors[] = new $preprocessor_class();
    }
  }

  public static function register_activation() {
    register_activation_hook(__FILE__, array(__CLASS__, 'install') );
  }

  public static function install() {
    self::assets_rewrite_rules();
    flush_rewrite_rules();
  }

  /**
   * Register all the actions we need to setup custom rewrite rules
   */
  public static function register_preprocessor_actions() {
    add_action('init', array(__CLASS__, 'assets_rewrite_rules'));
    add_action('query_vars', array(__CLASS__, 'query_vars'));
    add_action('parse_request', array(__CLASS__, 'parse_request'));
  }

  /**
   * Register some custom query vars we need to handle multiplexing of file preprocessors
   */
  public static function query_vars($query_vars) {
    foreach (self::$preprocessors as $preprocessor) {
      /* this query_var will be set to true when the requested URL needs this preprocessor */
      array_push($query_vars, $preprocessor->query_var_name());
      /* this query_var will be set to the url of the file preprocess */
      array_push($query_vars, $preprocessor->query_var_name('original_url'));
    }
    return $query_vars;
  }

  /**
   * For each preprocessor, it creates a new rewrite rule.
   */
  public static function assets_rewrite_rules() {
    foreach (self::$preprocessors as $preprocessor) {
      add_rewrite_rule('^(.*\.'.$preprocessor->to_extension().')$', 'index.php?'.$preprocessor->query_var_name().'=true&'.$preprocessor->query_var_name('original_url').'=$matches[1]', 'top');
    }
  }

  /**
   * If we get back our custom query vars, then redirect the request to the preprocessor.
   */
  public static function parse_request(&$wp) {
    foreach (self::$preprocessors as $preprocessor) {
      if (array_key_exists($preprocessor->query_var_name(), $wp->query_vars)) {
        $original_url = $wp->query_vars[$preprocessor->query_var_name('original_url')];
        $relative_path = str_replace(preg_replace("/^\//", "", self::theme_url()), '', $original_url);
        $processed_file_path = Wordless::join_paths(get_template_directory(), $relative_path);
        $relative_path = preg_replace("/^.*\/assets\//", "", $relative_path);
        $to_process_file_path = Wordless::join_paths(self::theme_assets_path(), $relative_path);
        $to_process_file_path = preg_replace("/\." . $preprocessor->to_extension() . "$/", "", $to_process_file_path);
        $preprocessor->process_file_with_caching($to_process_file_path, $processed_file_path, Wordless::theme_temp_path());
        exit();
      }
    }
  }

  /**
   * Set a Wordless preference
   */
  public static function set_preference($name, $value) {
    self::$preferences[$name] = $value;
  }

  /**
   * Get a Wordless preference
   */
  public static function preference($name, $default = '') {
    return isset(self::$preferences[$name]) ? self::$preferences[$name] : $default;
  }

  public static function load_i18n() {
    $locales_path = self::theme_locales_path();
    if (file_exists($locales_path) && is_dir($locales_path)) {
      load_theme_textdomain('we', $locales_path);
    }
  }

  public static function require_helpers() {
    require_once Wordless::join_paths(dirname(__FILE__), "helpers.php");
    $helpers_path = self::theme_helpers_path();
    self::require_once_dir("$helpers_path");
  }

  public static function require_theme_initializers() {
    $initializers_path = self::theme_initializers_path();
    self::require_once_dir("$initializers_path");
  }

  /**
   * Require one directory
   * @param string $path
   */
  public static function require_once_dir($path) {
    $list_files = glob(Wordless::join_paths($path, "*.php"));
    if (is_array($list_files)) {
        foreach ($list_files as $filename) {
          require_once $filename;
        }
    }
  }

  public static function theme_is_wordless_compatible() {
    $required_directories = array(
      self::theme_helpers_path(),
      self::theme_initializers_path(),
      self::theme_locales_path(),
      self::theme_views_path(),
      self::theme_assets_path(),
      self::theme_stylesheets_path(),
      self::theme_javascripts_path(),
      self::theme_temp_path()
    );
    foreach ($required_directories as $dir) {
      if (!file_exists($dir) || !is_dir($dir)) {
        return false;
      }
    }
    return true;
  }

  public static function theme_helpers_path() {
    return self::join_paths(get_template_directory(), 'theme/helpers');
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

  public static function theme_assets_path() {
    return self::join_paths(get_template_directory(), 'theme/assets');
  }

  public static function theme_stylesheets_path() {
    return self::join_paths(get_template_directory(), 'theme/assets/stylesheets');
  }

  public static function theme_javascripts_path() {
    return self::join_paths(get_template_directory(), 'theme/assets/javascripts');
  }

  public static function theme_static_javascripts_path() {
    return self::join_paths(get_template_directory(), 'assets/javascripts');
  }

  public static function theme_temp_path() {
    return self::join_paths(get_template_directory(), 'tmp');
  }

  public static function theme_url() {
    return str_replace(home_url(), '', get_bloginfo('template_url'));
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


