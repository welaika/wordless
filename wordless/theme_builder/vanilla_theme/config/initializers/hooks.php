<?php

/*
 * Place here all your WordPress add_filter() and add_action() calls.
 */

// Remove the WordPress Generator Meta Tag
function wordless_remove_generator_filter() { return ''; }
if (function_exists('add_filter')) {
  $types = array('html', 'xhtml', 'atom', 'rss2', /*'rdf',*/ 'comment', 'export');

  foreach ($types as $type)
    add_filter('get_the_generator_' . $type, 'wordless_remove_generator_filter');
}
