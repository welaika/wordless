<?php
/**
 * Abstract class, not meant to be used directly but to be extended to create
 * the preprocessors needed to compile project files (as SASS/SCSS to CSS or
 * Coffeescript to Javascript).
 *
 * @copyright welaika &copy; 2011 - MIT License
 */
class WordlessPreprocessor {

  /**
   * A dictionary of default values for preferences needed by the preprocessor.
   */
  private $preferences_defaults = array();

  public function __construct() {
    $this->set_preference_default_value("assets.cache_enabled", true);
  }

  /**
   * Returns the path for the file that caches a the result
   * of a previous compilation of the same asset file.
   *
   * @param string $file_path
   *   The path of the asset file to be retrieved from cache.
   *
   * @return string
   *   The path of the cached compilation result.
   *
   * @see Wordless::join_paths()
   * @see WordlessPreprocessor::asset_hash()
   * @see WordlessPreprocessor::class_name()
   */
  private function cached_file_path($file_path, $cache_path) {
    $cached_file_path = $this->class_name() . "_" . $this->asset_hash($file_path);
    return Wordless::join_paths($cache_path, $cached_file_path);
  }

  /**
   * Return the name of the class ( based on the instance ).
   *
   * @return string
   *   The name of the class.
   */
  public function class_name() {
    return strtolower(get_class($this));
  }

  /**
   * Define the MIME content type of compiled files.
   *
   * @attention Must be override by implemented preprocessors.
   *
   * @return string
   *   A string representing the MIME time of compiled files.
   */
  protected function content_type() {
    return "";
  }

  /**
   * Create a commented line.
   *
   * @attention Must be override by implemented preprocessors.
   *
   * @param string $line
   *   The line to be printed as comment
   *
   * @return string
   *   Return the commented line
   */
  protected function comment_line($line) {
    return $line;
  }

  /**
   * Return an unique hash for $file_path.
   *
   * The hash is guaranteed to be unique for the same file content
   * and preprocessor preferences. It might be necessary to override this
   * default implementation if the asset file depends on some secondary
   * file.
   *
   * @param string $file_path
   *   The path of the file for which generate the hash.
   *
   * @return string
   *   The hash itself.
   *
   *   * @see WordlessPreprocessor::preference()
   *
   */
  protected function asset_hash($file_path) {
    // First we get the file content
    $file_content = file_get_contents($file_path);

    // Then we attach the preferences
    foreach ($this->preferences_defaults as $pref => $value) {
      $file_content .= $pref . '=' . $this->preference($pref) . ';';
    }

    return md5($file_content);
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
  protected function folder_tree($path, $pattern = '*', $flags = 0) {
    $files = glob($path . $pattern, $flags);
    if (!is_array($files)) {
      $files = array();
    }
    $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);

    if (!empty($paths)) {
      foreach ($paths as $sub_path) {
        $subfiles = $this->folder_tree($sub_path . DIRECTORY_SEPARATOR, $pattern, $flags);
        if (is_array($subfiles)) {
          $files = array_merge($files, $subfiles);
        }
      }
    }

    return $files;
  }

  /**
   * Handy wrapper to retrieve a preference from the Wordless::preference()
   * method.
   *
   * If the preference is not set, the returned value will be the one specified
   * using the WordlessPreprocessor::set_preference_default_value() method.
   *
   * @see Wordless:preference()
   * @see Wordless:set_preference_default()
   */
  protected function preference($name) {
    return Wordless::preference($name, $this->preferences_defaults[$name]);
  }

  /**
   * Saves the default value for a custom preference.
   * @param string $name
   *   The preference name
   * @param string $value
   *   The preference default value
   */
  protected function set_preference_default_value($name, $value) {
    $this->preferences_defaults[$name] = $value;
  }

  /**
   * That's the main call of a WordPressPreprocessor subclass.
   *
   * @attention Must be override by implemented preprocessors.
   *
   * @param string $file_path
   *   The path to the file to process.
   * @param string $result_path
   *   The path in which save the processed file.
   * @param string $cache_path
   *   The directory path to use to store cached results.
   *
   * @return string
   *   Returns the content of the processed file.
   *
   */
  protected function process_file($file_path, $result_path, $cache_path) {
    return NULL;
  }

  /**
   * Process a file using a cache layer for optimized performances.
   *
   * Uses the process_file() function to perform file processing. Before process
   * check for a unchanged cache version of the processed file, to avoid
   * multiple processing of unchanged files.
   *
   * @param string $file_path_without_extension
   * @param string $result_path
   * @param string $cache_path
   *
   * @see Wordless::preference()
   * @see WordlessPreprocessor::cached_file_path()
   * @see WordlessPreprocessor::comment_line()
   * @see WordlessPreprocessor::content_type()
   * @see WordlessPreprocessor::die_with_error()
   * @see WordlessPreprocessor::process_file()
   * @see WordlessPreprocessor::supported_extensions()
   */
  public function process_file_with_caching($file_path_without_extension, $result_path, $cache_path) {
    header("Content-Type: " . $this->content_type());
    foreach ($this->supported_extensions() as $extension) {
      $file_path = $file_path_without_extension . ".$extension";
      if (is_file($file_path)) {
        // We check we already processed the same file previously
        $cached_file_path = $this->cached_file_path($file_path, $cache_path);
        // On cache hit
        if ($this->preference('assets.cache_enabled') && file_exists($cached_file_path)) {
          // Just return the cache result
          echo $this->comment_line("This is a cached version!") . file_get_contents($cached_file_path);
        } else {
          $start = time();
          $result = $this->process_file($file_path, $result_path, $cache_path);
          $end = time();
          file_put_contents($cached_file_path, $result);
          echo $this->comment_line(sprintf("Compile time: < %d secs", $end - $start)) . $result;
        }
        die();
      }
    }
    $this->die_with_error("File '$file_path_without_extension.(".join("|", $this->supported_extensions()).")' does not exists!");
  }

  /**
   * A function that creates custom WP query var names for this preprocessor.
   *
   * @param string $suffix
   *   A custom query var suffix
   *
   * @return string
   *   The WP query var name
   */
  public function query_var_name($suffix = '') {
    $chunks = array('wordless', $this->class_name(), 'precompiler');
    if ($suffix) $chunks[] = $suffix;
    return implode('_', $chunks);
  }

  /**
   * Define the supported extensions of the preprocessor.
   *
   * @attention Must be override by implemented preprocessors.
   *
   * @return array
   *   The array of supported extensions.
   */
  public function supported_extensions() {
    return array();
  }

  /**
   * Check consistency of the executable file used to process files.
   *
   * If the executable has not correct permissions thrown die_with_errors().
   *
   * @param string $path
   *   The path to the preprocessor executable file
   *
   * @doubt maybe is better if returns TRUE on success?
   * @warning die_with_error() is not defined in this class!
   */
  protected function validate_executable_or_die($path) {
    if (!is_executable($path)) {
      $this->die_with_error(sprintf(
        __("The path %s doesn't seem to be an executable!"),
        $path
      ));
    }
  }

   /**
   * Thrown in case of errors to die "nicely" displaying errors.
   *
   * If error occurred this function is thrown to display errors and stop
   * processing.
   *
   * @param string $description
   *   The description of the occurred error
   */
  protected function die_with_error($description) {
    echo sprintf("body::before { content: '%s'; font-family: monospace; };", addslashes($description));
    die();
  }

  /**
   * Define the extension of processed file.
   */
  public function to_extension() {
    return "css";
  }



}
