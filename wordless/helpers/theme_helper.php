<?php
/**
 * This module provides functions to get information from the curren theme.
 * 
 * @ingroup helperclass
 */
class ThemeHelper {
  /**
   * Returns the folder theme name based on the theme path.
   * 
   * The function use the whole theme path to get only the folder name of the
   * current theme.
   * 
   * @return string
   *   The folder name for the current theme.
   * 
   * @ingroup helperfunc
   */
  function get_theme_name() {
    return get_template();
  }

  /**
   * Returns the absolute path to the current theme.
   * 
   * Path without trailing slash.
   * 
   * @return string
   *   The absolute path to the current theme.
   * 
   * @ingroup helperfunc
   */
  function get_theme_path() {
    return get_template_directory();
  }

  /**
   * Returns the version of the the current theme.
   * 
   * @return string
   *   The version of the current theme.
   * 
   * @ingroup helperfunc
   */
  function get_theme_version() {
    if (class_exists('WP_Theme')) {
      $theme = new WP_Theme(get_theme_name(), get_theme_root());
      return $theme->get('Version');
    }
    else {
      $theme_data = get_theme_data(get_template_directory() . '/style.css');
      return $theme_data['Version'];
    }
  }
}

Wordless::register_helper("ThemeHelper");
