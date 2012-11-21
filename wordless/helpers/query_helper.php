<?php
/**
 * This module provides methods for quering the WP database.
 *
 * @ingroup helperclass
 */
class QueryHelper {

  /**
   * Get last posts of specified type.
   * 
   * @param string $type
   *   Post type used to filter result posts.
   * @param int $limit (optional)
   *   Maximum number of post to be retrieved.
   * @param string $order (optional)
   *   The order in which sort the post retrieved. @l{http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters, See WP docs for list of available values}.
   * @param string $ord (optional)
   *   Can be ASC for ascending or DESC for descending.
   * 
   * @return array
   *   Last posts of the selected type ordered.
   * 
   * @see http://codex.wordpress.org/Class_Reference/WP_Query#Parameters
   * 
   * @ingroup helperfunc
   */
  function latest_posts_of_type($type, $limit = -1, $order = 'date', $ord = 'DESC') {
    $wp_query = new WP_Query(array(
      "posts_per_page" => $limit,
      "post_type" => $type,
      "orderby" => $order,
      "order" => $ord
      ));
    return $wp_query;
  }

  /**
   * Get the last post of the specified type.
   * 
   * @param string $type
   *   See QueryHelper::latest_posts_of_type().
   * @param string $order (optional)
   *   See QueryHelper::latest_posts_of_type().
   * 
   * @return ?? 
   *   Last post of the selected type ordered.
   * 
   * @ingroup helperfunc
   */
  function latest_post_of_type($type, $order = 'date') {
    return latest_posts_of_type($type, 1, $order);
  }

  /**
   * Get last posts of specified category.
   * 
   * @param string $category
   *   Name of the category used to filter posts.
   * @param int $limit
   *   Maximum number of post to be retrieved.
   * @param int $offset (optional)
   *   Number of post to skip while retrieveing results.
   * @param string $post_type (optional)
   *   Filter results by post type.
   * @param string $taxonomy (optional)
   *   Taxonomy to query to retrieve posts.
   * @param string $order (optional)
   *   The order in which sort the post retrieved. @l{http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters, See WP docs for list of available values}.
   * @param string $ord (optional)
   *   Can be ASC for ascending or DESC for descending.
   * 
   * @return array
   *   Last posts of the selected category ordered.
   * 
   * @see http://codex.wordpress.org/Class_Reference/WP_Query#Parameters
   * 
   * @ingroup helperfunc
   */
  function latest_posts_of_category($category, $limit, $offset = 0, $post_type = 'post', $taxonomy = 'category', $order = 'date', $ord = 'DESC') {
    $wp_query = new WP_Query(array(
      'posts_per_page' => $limit,
      'tax_query' => array(
         array(
          'taxonomy' => $taxonomy,
          'field' => 'slug',
          'terms' => $category,
        ),
      ),
      'offset' => $offset,
      'post_type' => $post_type,
      'orderby' => $order,
      'order' => $ord
    ));
    return $wp_query;
  }

  /**
   * Get the last post of the specified category.
   * 
   * @param string $category
   *   See QueryHelper::latest_posts_of_category().
   * @param string $post_type
   *   See QueryHelper::latest_posts_of_category().
   * @param string $taxonomy (optional)
   *   See QueryHelper::latest_posts_of_category().
   * 
   * @return ??
   *   Last post of the selected category ordered.
   * 
   * @ingroup helperfunc
   */
  function latest_post_of_category($category, $post_type = 'post', $taxonomy = 'category') {
    return latest_posts_of_category($category, 1, 0, $post_type, $taxonomy);
  }

  /**
   * Check if post type is the one specified.
   * 
   * @param string $type
   *   Type of the post to be compared.
   * 
   * @return boolean
   *   TRUE if the post type of the global $post var is equal to the one passed
   *   as argument, FALSE otherwise.
   * 
   * @ingroup helperfunc
   */
  function is_post_type($type) {
    global $post;
    return $post->post_type == $type;
  }

  /**
   * Get the first categories, except one.
   * 
   * @param int $limit
   *   The max number of categories to be retrieved.
   * @param string $except
   *   The name of the category to exclude.
   * 
   * @return string
   *   A comma separated list of categories or an empty link if
   *         no category can be found.
   * 
   * @ingroup helperfunc
   */
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

  /**
   * Returns the page ID from page title.
   * 
   * @param string $title
   * 
   * @return int
   *   The ID of the page
   * 
   * @ingroup helperfunc
   */
  function get_page_id_by_title($title) {
    $page = get_page_by_title($title);
    return $page->ID;
  }

  /**
   * Returns the category ID from the category name.
   * 
   * @param string $cat_name
   *   The name of the category
   * @param string $taxonomy (optional)
   *   The taxonomy at which the category belongs to.
   * 
   * @return int
   *   The ID of the category
   * 
   * @ingroup helperfunc
   */ 
  function get_category_id_by_name($cat_name, $taxonomy = 'category'){
    $term = get_term_by('name', $cat_name, $taxonomy);
    return $term->term_id;
  }

  /**
   * Returns a link to the specified category.
   * 
   * @param string $cat_name
   *   The name of the category.
   * @param string $taxonomy (optional)
   *   The taxonomy at which the category 
   *        belongs to
   * 
   * @return int
   *   A link to the category.
   * 
   * @ingroup helperfunc
   */
  function get_category_link_by_name($cat_name, $taxonomy = 'category') {
    $id = get_category_id_by_name($cat_name, $taxonomy);
    return get_category_link($id);
  }

  /**
   * Returns the content of the current post. Must be called within The Loop.
   * 
   * @return string
   *   The content of the current post.
   * 
   * @ingroup helperfunc
   */
  function get_the_filtered_content() {
    ob_start();
    the_content();
    return ob_get_clean();
  }

  /**
   * Returns the post type (always singular).
   * 
   * @return string
   *   The post name.
   * 
   * @ingroup helperfunc
   */
  function get_post_type_singular_name() {
    $obj = get_post_type_object(get_post_type());
    return $obj->labels->name;
  }

  /**
   * Returns the page title.
   * 
   * @param string $prefix (optional)
   *   A string to be prefixed to the current page title.
   * @param string $separator (optional)
   *   A string to separate prefix and current page title.
   * 
   * @return string
   *   The page title
   * 
   * @ingroup helperfunc
   */
  function get_page_title($prefix = "", $separator = "") {
    $title = "";
    if (is_category()) {
      $category = get_category(get_query_var('cat'),false);
      $title = get_cat_name($category->cat_ID);
    }
    if (is_post_type_archive()) {
      $title = get_post_type_singular_name();
    }
    if (is_single() || is_page()) {
      $title = get_the_title();
    }
    if (is_search()) {
      $title = sprintf(__("Search: %s", "we"), get_search_query());
    }
    if (is_date()) {
      if (is_month()) {
        $date = get_the_date("F Y");
      } else if (is_year()) {
        $date = get_the_date("Y");
      } else {
        $date = get_the_date();
      }
      $title = sprintf(__("Archives: %s", "we"), $date);
    }
    if (is_front_page() || is_home()) {
      $title = get_bloginfo("description", "display");
    }
    if ($title != "") {
      return "$prefix$separator$title";
    } else {
      return $prefix;
    }
  }

}

Wordless::register_helper("QueryHelper");
