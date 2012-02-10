<?php
/**
 * This module provides methods for accesing theme URL path.
 *
 * @ingroup helperclass
 */
class UrlHelper {

  /**
   * Returns the URL path to the spcified folder in the assets directory.
   * 
   * @param string $path
   *   The path inside the @e {theme}/assets/ folder.
   * 
   * @return string
   *   The complete URL path to the specified folder.
   */
  function asset_url($path) {
    return get_bloginfo('stylesheet_directory') . "/assets/$path";
  }

  /**
   * Returns the URL path to the specified folder in the images directory.
   * 
   * @param string $path
   *   The path inside the @e {theme}/assets/images/ folder.
   * 
   * @return string
   *   The complete URL path to the specified folder.
   */
  function image_url($path) {
    return asset_url("images/$path");
  }

  /**
   * Returns the URL path to the specified folder in the stylesheet directory.
   * 
   * @param string $path
   *   The path inside the @e {theme}/assets/stylesheet/ folder.
   * 
   * @return string
   *   The complete URL path to the specified folder.
   */
  function stylesheet_url($path) {
    return asset_url("stylesheets/$path");
  }

  /**
   * Returns the URL path to the specified folder in the javascript directory.
   * 
   * @param string $path
   *   The path inside the @e {theme}/assets/javascript/ folder.
   * 
   * @return string
   *   The complete URL path to the specified folder.
   */
  function javascript_url($path) {
    return asset_url("javascripts/$path");
  }
}

Wordless::register_helper("UrlHelper");
