<?php

// This function include screen.css in wp_head() function

function enqueue_stylesheets() {
  wp_register_style("screen", stylesheet_url("screen"), false, false);
  wp_enqueue_style("screen");
}

add_action('wp_enqueue_scripts', 'enqueue_stylesheets');