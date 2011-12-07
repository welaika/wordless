<?php

/*
 * Enable WordPress menu support uncommenting the line below
 */
// add_theme_support('menus');

function register_custom_menus() {
  /*
   * Place here all your register_nav_menu() calls.
   */

  //register_nav_menu('main_menu', 'Header Menu');
}

add_action('init', 'register_custom_menus');

