<?php

function lastest_posts_of_type($type, $limit = -1, $order = 'date') {
  return query_posts("posts_per_page=$limit&post_type=$type&orderby=$order");
}

function lastest_post_of_type($type, $order = 'date') {
  $posts = lastest_posts_of_type($type, 1, $order);
  return $posts[0];
}

function latest_posts_of_category($category, $limit, $offset = 0, $post_type = 'post', $taxonomy = 'category', $order = 'date') {
  return query_posts(array(
    'posts_per_page' => $limit,
    'taxonomy' => $taxonomy,
    'term' => $category,
    'offset' => $offset,
    'post_type' => $post_type,
    'orderby' => $order
  ));
}

function latest_post_of_category($category, $post_type = 'post', $taxonomy = 'category') {
  $posts = latest_posts_of_category($category, 1, 0, $post_type, $taxonomy);
  return $posts[0];
}

function get_the_first_categories_except($limit, $except) {
  global $post;
  $categories = get_the_category();
  $found_categories = false;

  if (count($categories)) {
    $filtered_categories = array();
    foreach ($categories as $category) {
      if ($category->cat_name != $except and count($filtered_categories) < $limit) {
        $filtered_categories[] = link_to($category->cat_name, get_category_link($category->cat_ID));
        $found_categories = true;
      }
    }
  }

  if ($found_categories) {
    return join(", ", $filtered_categories);
  } else {
    return link_to("Articolo", "#");
  }
}

function get_page_id_by_title($title) {
  $page = get_page_by_title($title);
  return $page->ID;
}

function get_category_id_by_name($cat_name, $taxonomy = 'category'){
  $term = get_term_by('name', $cat_name, $taxonomy);
  return $term->term_id;
}

function get_category_link_by_name($cat_name, $taxonomy = 'category') {
  $id = get_category_id_by_name($cat_name, $taxonomy);
  return get_category_link($id);
}

function get_the_filtered_content() {
  ob_start();
  the_content();
  return ob_get_clean();
}
