<?php

/**
 * That is the base, abstract class for each Wordless preprocessor engine.
 **/
class WordlessPreprocessor
{

  protected $preferences = array();

  public function class_name() {
    return strtolower(get_class($this));
  }

  public function query_var_name($suffix = '') {
    $chunks = array('wordless_'.$this->class_name().'_precompiler');
    if ($suffix) $chunks[] = $suffix;
    return implode('_', $chunks);
  }

  public function supported_extensions() {
    return array();
  }

  public function process_file($file_path, $result_path, $temp_path) {
    // No-op
  }

  public function pref($name, $default = '') {
    return Wordless::preference($name, $default ? $default : $this->preferences[$name]);
  }

  public function content_type() {
    return "";
  }

  public function comment_line($line) {
    return $line;
  }

  public function cache_hash($file_path) {
    // First we get the file content
    $file_content = file_get_contents($file_path);

    // Then we attach the preferences
    foreach ($this->preferences as $pref => $value) {
      $file_content .= $pref . '=' . $this->pref($pref) . ';';
    }

    return $file_content;
  }

  public function cached_file_path($file_path, $cache_path) {
    $cached_file_path = $this->class_name() . "-" . md5($this->cache_hash($file_path));
    return Wordless::join_paths($cache_path, $cached_file_path);
  }

  public function validate_executable($path) {
    if (!is_executable($path)) {
      $this->die_with_error(sprintf(
        __("The path %s doesn't seem to be an executable!"),
        $path
      ));
    }
  }

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


}
