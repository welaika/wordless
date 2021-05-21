<?php

// This function include main.css in wp_head() function

function enqueue_stylesheets() {
  wp_register_style("main", stylesheet_url("main"), [], false, 'all');
  wp_enqueue_style("main");
}

add_action('wp_enqueue_scripts', 'enqueue_stylesheets');

// This function include jquery and main.js in wp_footer() function

function enqueue_javascripts() {
  wp_enqueue_script("jquery");
  wp_register_script("main", javascript_url("main"), [], false, true);
  wp_enqueue_script("main");
}

add_action('wp_enqueue_scripts', 'enqueue_javascripts');

// Load theme supports
// See http://developer.wordpress.org/reference/functions/add_theme_support/
// for more theme supports you'd like to add. `reponsive-embeds` is on by
// default.
function wordless_theme_supports() {
  add_theme_support('responsive-embeds');
}
add_action('after_setup_theme', 'wordless_theme_supports');
