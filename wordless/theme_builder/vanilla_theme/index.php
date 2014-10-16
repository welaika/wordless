<?php

/*
 * Making sure Wordless plugin is enabled
 */

if (!class_exists("Wordless")) {
  echo "This theme requires the <a href='https://github.com/welaika/wordless'>Wordless plugin</a> in order to work. Please, install it now!";
  die();
}

/*
 * In this page, you need to setup Wordless routing: you first
 * determine the type of the page using WordPress conditional tags,
 * and then delegate the rendering to some particular view using
 * the `render_view()` helper.
 *
 * To specify a layout other than the default one, please pass it as
 * the second parameter to the `render_view()` method.
 *
 * For a list of conditional tags, please see here: http://codex.wordpress.org/Conditional_Tags
 */

if (is_single()) {
  render_view("posts/single");
} else if (is_front_page()){
  render_view("posts/single");
}else if (is_archive()) {
  render_view("posts/archive");
} else {
  render_view("posts/404");
}

