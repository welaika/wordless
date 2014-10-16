<?php

function add_custom_post_types() {
  /*
   * Create here your custom post types. You can both use the WordPress register_post_type()
   * function, or the Wordless new_post_type() helper.
   */

  // new_post_type("portfolio_work", array('title', 'editor'));
}

function add_custom_taxonomies() {
  /*
   * Create here your custom post types. You can both use the WordPress register_taxonomy()
   * function, or the Wordless new_taxonomy() helper.
   */

  // new_taxonomy("work_type", array('portfolio_work'));
}

add_action('init', 'add_custom_post_types');
add_action('init', 'add_custom_taxonomies');


