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
        self::register_preprocessors();
        self::register_preprocessor_actions();
    }
    self::load_admin_page();
    self::register_plugin_i18n();
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
    $preprocessors = array_filter(self::preference("assets.preprocessors", array("SprocketsPreprocessor", "CompassPreprocessor")));
    foreach ($preprocessors as $preprocessor_class) {
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

  public static function register_plugin_i18n() {
    add_action('init', array(__CLASS__, 'plugin_i18n'));
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
        $relative_path = preg_replace("/^.*\/assets\//", "", $relative_path);
        $to_process_file_path = Wordless::join_paths(self::theme_assets_path(), $relative_path);
        $to_process_file_path = preg_replace("/\." . $preprocessor->to_extension() . "$/", "", $to_process_file_path);
        $preprocessor->serve_compiled_file($to_process_file_path, Wordless::theme_temp_path());
        return;
      }
    }
  }

  public static function compile_assets() {
    foreach (self::$preprocessors as $preprocessor) {
      foreach ($preprocessor->supported_extensions() as $extension) {
        $list_files = self::recursive_glob(self::theme_assets_path(), "*.$extension");
        foreach ($list_files as $file_path) {
          // Ignore partials
          if (!preg_match("/^_/", basename($file_path))) {
            $compiled_file_path = str_replace(Wordless::theme_assets_path(), '', $file_path);
            $compiled_file_path = Wordless::join_paths(Wordless::theme_static_assets_path(), $compiled_file_path);
            $compiled_file_path = preg_replace("/\." . $extension . "$/", ".".$preprocessor->to_extension(), $compiled_file_path);

            try {
              $to_process_file_path = preg_replace("/\." . $extension . "$/", "", $file_path);
              $compiled_content = $preprocessor->process_file_with_caching($to_process_file_path, Wordless::theme_temp_path());
            } catch(WordlessCompileException $e) {
              echo "Problems compiling $file_path to $compiled_file_path\n\n";
              echo $e;
              echo "\n\n";
            }

            file_put_contents($compiled_file_path, $compiled_content);
          }
        }
      }
    }
  }

  /**
   * Recursively searches inside a directory for specific files.
   *
   * * @param string $directory_path
   *   The path of the directory to search recursively
   * * @param string $pattern
   *   The glob pattern of the files (see http://php.net/manual/en/function.glob.php)
   * * @param int $flags
   *   The glob search flags (see http://php.net/manual/en/function.glob.php)
   *
   */
  public static function recursive_glob($path, $pattern = '*', $flags = 0) {
    $files = glob(self::join_paths($path, $pattern), $flags);

    if (!is_array($files)) {
      $files = array();
    }

    $paths = glob(self::join_paths($path, '*'), GLOB_ONLYDIR | GLOB_NOSORT);

    if (!empty($paths)) {
      foreach ($paths as $sub_path) {
        $subfiles = self::recursive_glob($sub_path, $pattern, $flags);
        if (is_array($subfiles)) {
          $files = array_merge($files, $subfiles);
        }
      }
    }

    return $files;
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
    $theme_locales_path = self::theme_locales_path();
    if (file_exists($theme_locales_path) && is_dir($theme_locales_path)) {
      load_theme_textdomain('we', $theme_locales_path);
    }
  }

  public static function plugin_i18n() {
    $plugin_locales_rel_path = self::join_paths('wordless', 'locales');
    load_plugin_textdomain('we', false, $plugin_locales_rel_path);
  }

  public static function require_helpers() {
    require_once Wordless::join_paths(dirname(__FILE__), "helpers.php");
    $helpers_path = self::theme_helpers_path();
    self::require_once_dir($helpers_path);
  }

  public static function require_theme_initializers() {
    $initializers_path = self::theme_initializers_path();
    self::require_once_dir($initializers_path);
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

  /**
   * Checks for required directories when initializing theme. If any are missing, it will return false.
   * If passed `true`, this function will return an array of missing directories.
   *
   * * @param boolean $return_array
   *   Set true to get a list of missing directories
   *
   */
  public static function theme_is_wordless_compatible($return_array = false) {
    // Require wordless_preferences.php in case the user has changed the below paths.
    $wordless_preferences_path = self::join_paths(self::theme_initializers_path(), "wordless_preferences.php");
    if(file_exists($wordless_preferences_path)){
      require_once $wordless_preferences_path;
    }

    $missing = self::get_theme_missing_directories();

    if(!empty($missing)){
      return ($return_array) ? $missing : false;
    }

    return true;    
  }

  /**
  * Return directories missing. Empty array if nothing is missing.
  */
  public static function get_theme_missing_directories(){
    $missing = array();

    // Scan required directories.
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
        $missing[] = $dir;
      }
    }

    return $missing;
  }

  public static function plugin_path() {
    return self::join_paths(WP_CONTENT_DIR, 'plugins', 'wordless');
  }

  public static function theme_path() {
    return get_template_directory();
  }  

  public static function theme_helpers_path() {
    return self::join_paths(self::theme_path(), 'theme/helpers');
  }

  public static function theme_initializers_path() {
    return self::join_paths(self::theme_path(), 'config/initializers');
  }

  public static function theme_locales_path() {
    return self::join_paths(self::theme_path(), 'config/locales');
  }

  public static function theme_views_path() {
    return self::join_paths(self::theme_path(), 'theme/views');
  }

  public static function theme_assets_path() {
    return self::join_paths(self::theme_path(), 'theme/assets');
  }

  public static function theme_stylesheets_path() {
    return self::join_paths(self::theme_path(), 'theme/assets/stylesheets');
  }

  public static function theme_javascripts_path() {
    return self::join_paths(self::theme_path(), 'theme/assets/javascripts');
  }

  public static function theme_static_assets_path() {
    return self::join_paths(self::theme_path(), 'assets');
  }

  public static function theme_static_javascripts_path() {
    return self::join_paths(self::theme_path(), 'assets/javascripts');
  }

  public static function theme_temp_path() {
    return self::preference("theme.temp_dir", self::join_paths(self::theme_path(), 'tmp'));
  }

  public static function theme_url() {
    return parse_url(get_bloginfo('template_url'), PHP_URL_PATH);
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


