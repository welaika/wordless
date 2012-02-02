<?php

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
  $temp = explode("wp-content/themes/", get_bloginfo("template_url"));
  return $temp[1];  // The second value will be the theme name
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
  return get_theme_root() . '/' . get_theme_name();
}
