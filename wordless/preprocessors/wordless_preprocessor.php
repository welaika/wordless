<?php
/**
 * @file
 * Define an abstract class for each Wordless preprocessor engine.
 */

/**
 * Abstract class, not meant to be used directly but to be extended to create 
 * the preprocessors needed to compile project files ( as SASS/SCSS to CSS or
 * Coffeescript to Javascript).
 * 
 * @copyright welaika &copy; 2011 - MIT License
 */
class WordlessPreprocessor {

  /**
   * An array of options used by preprocessors.
   */
  protected $preferences = array();


  /**
   * Get the correct path to a cached file.
   * 
   * @param string $file_path
   *   The path to the file to be retrieved from cache.
   * 
   * @return string
   *   The path to the cached file.
   * 
   * @see Wordless::join_paths()
   * @see WordlessPreprocessor::cache_hash()
   * @see WordlessPreprocessor::class_name()
   * 
   * @doubt I don't get why md5 is used here instead that into cache_hash()
   */
  public function cached_file_path($file_path, $cache_path) {
    $cached_file_path = $this->class_name() . "-" . md5($this->cache_hash($file_path));
    return Wordless::join_paths($cache_path, $cached_file_path);
  }

  /**
   * Return content of the specified file and preprocessor preferences.
   * 
   * This hash is used to cache files and avoid preprocessing unchanged files.
   * 
   * @param string $file_path
   *   The path to the file to check for cached version.
   * 
   * @return string
   *   The content of the file with attached preprocessor preferences as a 
   *   string.
   * 
   * @see WordlessPreprocessor::pref()
   * 
   * @doubt Would be better to change its name... It took me a while to 
   *   understand this functionality
   */
  public function cache_hash($file_path) {
    // First we get the file content
    $file_content = file_get_contents($file_path);

    // Then we attach the preferences
    foreach ($this->preferences as $pref => $value) {
      $file_content .= $pref . '=' . $this->pref($pref) . ';';
    }

    return $file_content;
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
   * @attention Must be override by implemented preprocessors. In this class 
   *   returns an empty string.
   * 
   * @return string
   *   A string representing the MIME time of compiled files.
   */
  public function content_type() {
    return "";
  }

  /**
   * Create a commented line.
   * 
   * @attention Must be override by implemented preprocessors. In this class 
   *   returns the line.
   * 
   * @param string $line
   *   The line to be printed as comment
   * 
   * @return string
   *   Return the commented line
   */
  public function comment_line($line) {
    return $line;
  }

  /**
   * @see WordlessPreprocessor::folder_tree()
   * 
   * @todo check docs
   */
  protected function folder_tree($pattern = '*', $flags = 0, $path = false, $depth = -1, $level = 0) {
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

  /**
   * Set a preference in the Wordless main object.
   * 
   * This method relies on Wordless::preference() method to set a preference 
   * which can be used in the whole scope of the Wordless object.
   * 
   * @see Wordless:preference()
   */
  public function pref($name, $default = '') {
    return Wordless::preference($name, $default ? $default : $this->preferences[$name]);
  }

  /**
   * Process a file, executing the define preprocessor.
   * 
   * @attention Must be override by implemented preprocessors. In this class
   *   does nothing, and return NULL.
   * 
   * @param string $file_path
   *   The path to the file to process.
   * @param string $result_path
   *   The path in which save the processed file.
   * @param string $temp_path
   *   The temporary path in which save some configurations.
   * 
   * @return string
   *   Returns the content of the processed file.
   * 
   * @todo check docs
   */
  public function process_file($file_path, $result_path, $temp_path) {
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
   * 
   * @doubt Is really needed to use die() to close this function? Its ok using
   *   die_with_errors(), but why use die() to kill a foreach?
   */
  public function process_file_with_caching($file_path_without_extension, $result_path, $cache_path) {
    header("Content-Type: " . $this->content_type());

    foreach ($this->supported_extensions() as $extension) {
      $file_path = $file_path_without_extension . ".$extension";
      if (is_file($file_path)) {

        // We check we already processed the same file previously
        $cached_file_path = $this->cached_file_path($file_path, $cache_path);

        // On cache hit
        if (Wordless::preference('assets.cache_enabled', true) && file_exists($cached_file_path)) {

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
   * @see WordlessPreprocessor::class_name()
   * 
   * @todo check docs
   */
  public function query_var_name($suffix = '') {
    $chunks = array('wordless_'.$this->class_name().'_precompiler');
    if ($suffix) $chunks[] = $suffix;
    return implode('_', $chunks);
  }

  /**
   * Define the supported extensions of the preprocessor.
   * 
   * @attention Must be override by implemented preprocessors. In this class 
   *   returns an empty array.
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
  public function validate_executable($path) {
    if (!is_executable($path)) {
      $this->die_with_error(sprintf(
        __("The path %s doesn't seem to be an executable!"),
        $path
      ));
    }
  }

}
