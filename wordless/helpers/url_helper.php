<?php
/**
 * This module provides methods for accesing theme URL path.
 *
 * @ingroup helperclass
 */
class UrlHelper {

  /**
   * Returns URL arguments
   * 
   * @param int $index (optional)
   *   The number (counting from zero) of the argument in the list. If is not
   *   specified  all arguments will be returned as an array.
   * @return array|mixed
   *   If $index was specified returns the relative URL argument, elsewhere 
   *   returns an array with all available URL arguments.
   * 
   * @ingroup helperfunc
   */
  function arg($index = NULL) {
    $args = array_values(array_filter(explode('/', $_SERVER['REQUEST_URI'])));

    if (isset($index))
      return $args[$index];
  
    return $args;
  }

  /**
   * Returns the URL path to the spcified folder in the assets directory.
   * 
   * @param string $path
   *   The path inside the @e {theme}/assets/ folder.
   * @return string
   *   The complete URL path to the specified folder.
   * 
   * @ingroup helperfunc
   */
  function asset_url($path) {
    return get_bloginfo('stylesheet_directory') . "/assets/$path";
  }

  /**
   * Returns the URL path to the specified folder in the images directory.
   * 
   * @param string $path
   *   The path inside the @e {theme}/assets/images/ folder.
   * @return string
   *   The complete URL path to the specified folder.
   * 
   * @ingroup helperfunc
   */
  function image_url($path) {
    return asset_url("images/$path");
  }

  /**
   * Returns the URL path to the specified folder in the stylesheet directory.
   * 
   * @param string $path
   *   The path inside the @e {theme}/assets/stylesheet/ folder.
   * @return string
   *   The complete URL path to the specified folder.
   * 
   * @ingroup helperfunc
   */
  function stylesheet_url($path) {
    return asset_url("stylesheets/$path");
  }

  /**
   * Returns the URL path to the specified folder in the javascript directory.
   * 
   * @param string $path
   *   The path inside the @e {theme}/assets/javascript/ folder.
   * @return string
   *   The complete URL path to the specified folder.
   * 
   * @ingroup helperfunc
   */
  function javascript_url($path) {
    return asset_url("javascripts/$path");
  }
}

Wordless::register_helper("UrlHelper");
