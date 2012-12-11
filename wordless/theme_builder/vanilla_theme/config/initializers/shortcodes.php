<?php

function shortcode_function($attrs) {

  /*
    Insert the function called by add_shortcode.
    $atts is the array of values passed by wordpress shortcode.
  */

}

function register_shortcodes(){
  
  /*
    Add new shortcode uncommenting the line below.
    The first value is the name of shortcode. The second is the function that calls.
  */

  // add_shortcode('shortcode', 'shortcode_function');

}

add_action( 'init', 'register_shortcodes');