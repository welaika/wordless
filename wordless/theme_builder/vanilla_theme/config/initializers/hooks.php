<?php

/*
 * Place here all your WordPress add_filter() and add_action() calls.
 */

/**
 * Add custom post types to default WP RSS feed.
 */
add_filter('request', 'add_cpts_to_rss_feed');
function add_cpts_to_rss_feed( $args ) {
  if ( isset( $args['feed'] ) && !isset( $args['post_type'] ) ) {
    $post_types = get_post_types(array('_builtin' => FALSE));
    $post_types['post'] = 'post';
    $args['post_type'] = $post_types;
  }
  
  return $args;
}
