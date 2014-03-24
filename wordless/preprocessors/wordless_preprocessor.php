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
  private $deprecated_preferences = array();

  public function __construct() {
    $this->verify_timezone();
    $this->set_preference_default_value("assets.cache_enabled", true);
  }

  /* Verify setting on date.timezone in your php.ini
   */

  protected function verify_timezone(){
    if(ini_get('date.timezone')){
      date_default_timezone_set(ini_get('date.timezone'));
    }
    else{
      date_default_timezone_set('UTC');
    }
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
    // First we get the file's modified date
    $hash_seed = date("%U", filemtime($file_path));

    // Then we attach the preferences
    foreach ($this->preferences_defaults as $pref => $value) {
      $hash_seed .= $pref . '=' . (is_array($this->preference($pref)) ? implode(" ", $this->preference($pref)) : $this->preference($pref)) . ';';
    }

    return md5($hash_seed);
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
    $possible_names = array($name);
    if (isset($this->deprecated_preferences[$name])) {
      $possible_names = $this->deprecated_preferences[$name];
      array_unshift($possible_names, $name);
    }

    foreach ($possible_names as $possible_name) {
      $value = Wordless::preference($possible_name, NULL);
      if (isset($value)) {
        return $value;
      }
    }

    return $this->preferences_defaults[$name];
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
   * Marks a preference name as deprecated.
   * @param string $old_preference_name
   *   The old preference name
   * @param string $new_preference_name
   *   The new preference name
   */
  protected function mark_preference_as_deprecated($old_preference_name, $new_preference_name) {
    if (!isset($this->deprecated_preferences[$new_preference_name])) {
      $this->deprecated_preferences[$new_preference_name] = array();
    }
    array_push($this->deprecated_preferences[$new_preference_name], $old_preference_name);
  }

  /**
   * That's the main call of a WordPressPreprocessor subclass.
   *
   * @attention Must be override by implemented preprocessors.
   *
   * @param string $file_path
   *   The path to the file to process.
   * @param string $cache_path
   *   The directory path to use to store cached results.
   *
   * @return string
   *   Returns the content of the processed file.
   *
   */
  protected function process_file($file_path, $cache_path) {
    return NULL;
  }

  /**
   * Serves the compiled file.
   *
   * @param string $file_path_without_extension
   *   The path to the file to process, with no extensions.
   * @param string $cache_path
   *   The directory path to use to store cached results.
   *
   */
  public function serve_compiled_file($file_path_without_extension, $cache_path) {
    header("Content-Type: " . $this->content_type());
    try {
      echo $this->process_file_with_caching($file_path_without_extension, $cache_path);
    } catch(WordlessCompileException $e) {
      echo $this->error($e->__toString());
    }
    die();
  }

  /**
   * Process a file using a cache layer for optimized performances.
   *
   * Uses the process_file() function to perform file processing. Before process
   * check for a unchanged cache version of the processed file, to avoid
   * multiple processing of unchanged files.
   *
   * @param string $file_path_without_extension
   * @param string $cache_path
   *
   * @return string
   *   Returns the content of the processed file.
   *
   * @see Wordless::preference()
   * @see WordlessPreprocessor::cached_file_path()
   * @see WordlessPreprocessor::comment_line()
   * @see WordlessPreprocessor::content_type()
   * @see WordlessPreprocessor::error()
   * @see WordlessPreprocessor::process_file()
   * @see WordlessPreprocessor::supported_extensions()
   */
  public function process_file_with_caching($file_path_without_extension, $cache_path) {
    foreach ($this->supported_extensions() as $extension) {
      $file_path = $file_path_without_extension . ".$extension";
      if (is_file($file_path)) {
        // We check we already processed the same file previously
        $cached_file_path = $this->cached_file_path($file_path, $cache_path);
        // On cache hit
        if ($this->preference('assets.cache_enabled') && file_exists($cached_file_path)) {
          // Just return the cache result
          return $this->comment_line("This is a cached version!") . file_get_contents($cached_file_path);
        } else {
          $start = time();
          $result = $this->process_file($file_path, $cache_path);
          $end = time();
          file_put_contents($cached_file_path, $result);
          return $this->comment_line(sprintf("Compile time: < %d secs", $end - $start)) . $result;
        }
        return;
      }
    }
    throw new WordlessCompileException(
      "File '$file_path_without_extension.(".join("|", $this->supported_extensions()).")' does not exists!"
    );
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
   * If the executable has not correct permissions thrown errors().
   *
   * @param string $path
   *   The path to the preprocessor executable file
   *
   * @doubt maybe is better if returns TRUE on success?
   * @warning error() is not defined in this class!
   */
  protected function validate_executable_or_throw($path) {
    if (!is_executable($path)) {
      throw new WordlessCompileException(sprintf(
        __("The path %s doesn't seem to be an executable!", "wl"),
        $path
      ));
    }
  }

   /**
   * Nicely render errors.
   *
   * @param string $description
   *   The description of the occurred error
   */
  protected function error($description) {
    return $description;
  }

  /**
   * Define the extension of processed file.
   */
  public function to_extension() {
    return "css";
  }



}
