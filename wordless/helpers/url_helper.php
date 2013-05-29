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
    if (!preg_match("/\.css$/", $path)) $path .= ".css";
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
    if (!preg_match("/\.js$/", $path)) $path .= ".js";
    return asset_url("javascripts/$path");
  }

  /**
   * Check if an URL is absolute or not
   * URL are considered absolute if they begin with a protocol specification
   * (https|https in this case) or with the double slash (//) to take advantage
   * of the protocol relative URL (http://paulirish.com/2010/the-protocol-relative-url/) 
   * 
   * @param string $url
   *   The url to check.
   * @return boolean
   *   Either true if the URL is absolute or false if it is not.
   * 
   * @ingroup helperfunc
   */

  function is_absolute_url($url) {
    return(preg_match("/^(https?:)?\/\//", $url) === 1);
  }


  /**
   * Check if an URL is root relative
   * URL are considered root relative if they are not absolute but begin with a /
   * 
   * @param string $url
   *   The url to check.
   * @return boolean
   *   Either true if the URL is root relative or false if it is not.
   * 
   * @ingroup helperfunc
   */

  function is_root_relative_url($url) {
    return(!is_absolute_url($url) && preg_match("/^\//", $url) === 1);
  }

  /**
   * Check if an URL is relative
   * URL are considered relative if they are not absolute and don't begin with a /
   * 
   * @param string $url
   *   The url to check.
   * @return boolean
   *   Either true if the URL is relative or false if it is not.
   * 
   * @ingroup helperfunc
   */

  function is_relative_url($url) {
    return(!(is_absolute_url($url) || is_root_relative_url($url)));
  }

}

Wordless::register_helper("UrlHelper");
