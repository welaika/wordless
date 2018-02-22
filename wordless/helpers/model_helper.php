<?php
/**
 * This module provides methods for handling of WordPress "models" ( Posts and
 * Taxonomies ), making easy to create new post types or new taxonomies.
 *
 * @ingroup helperclass
 */
class ModelHelper {

  /**
   * Creates a new post type.
   *
   * This function use the WP APIs and functions to register a new post type.
   *
   * @param string|array $name
   *   The name of the new post type (will appear in the backend). If the name
   *   is a sting, the plural will be evaluated by the system; if is an array,
   *   must contains the singular and the plural versions of the name.
   *   Ex:
   *   @code
   * $name = array(
   *   "singular" => 'My custom post type',
   *   "plural" => 'My custom post types'
   * );
   *    @endcode
   * @param array $supports (optional)
   *   Extra fields added to this post type. Default fields (the fields you can
   *   find in page/post type) are added by default.
   * @param array $options (optional)
   *   An optional array to override default options passed to
   *   register_post_type().
   *
   * @ingroup helperfunc
   */
  function new_post_type($name, $supports = array("title", "editor"), $options = array()) {

    if (!is_array($name)) {
      $name = array(
        "singular" => $name,
        "plural" => pluralize($name)
      );
    }

    $uc_plural = __(ucwords(preg_replace("/_/", " ", $name["plural"])), "wl");
    $uc_singular = __(ucwords(preg_replace("/_/", " ", $name["singular"])), "wl");

    $labels = array(
      'name' => $uc_plural,
      'singular_name' => $uc_singular,
      'add_new_item' => sprintf(__("Add new %s", "wl"), $uc_singular),
      'edit_item' => sprintf(__("Edit %s", "wl"), $uc_singular),
      'new_item' => sprintf(__("New %s", "wl"), $uc_singular),
      'view_item' => sprintf(__("View %s", "wl"), $uc_singular),
      'search_items' => sprintf(__("Search %s", "wl"), $uc_plural),
      'not_found' => sprintf(__("No %s found.", "wl"), $uc_plural),
      'not_found_in_trash' => sprintf(__("No %s found in Trash", "wl"), $uc_plural),
      'parent_item_colon' => ',',
      'menu_name' => $uc_plural
    );

    $options = array_merge(array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => $name["plural"]),
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => null,
        'show_in_rest' => true,
        'rest_base' => strtolower($uc_plural),
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports' => $supports
      ), $options);

    register_post_type(
      $name["singular"],
      $options
    );
  }

  /**
   * Create a new taxonomy.
   *
   * @param string $name
   *   The name of the taxonomy.
   * @param array|string $post_types
   *   Name of the object type for the taxonomy object. Object-types can be
   *   built-in objects (see below) or any custom post type that may be
   *   registered.
   * @param array $options (optional)
   *   An optional array to override default options passed to
   *   register_taxonomy().
   *
   * @ingroup helperfunc
   */
  function new_taxonomy($name, $post_types, $options = array()) {

    if (!is_array($name)) {
      $name = array(
        "singular" => $name,
        "plural" => pluralize($name)
      );
    }

    $uc_plural = __(ucwords(preg_replace("/_/", " ", $name["plural"])), "wl");
    $uc_singular = __(ucwords(preg_replace("/_/", " ", $name["singular"])), "wl");

    $labels = array(
      "name" => $uc_singular,
      "singular_name" => $uc_singular,
      "search_items" => sprintf(__("Search %s", "wl"), $uc_plural),
      "all_items" => sprintf(__("All %s", "wl"), $uc_plural),
      "parent_item" => sprintf(__("Parent %s", "wl"), $uc_singular),
      "parent_item_colon" => sprintf(__("Parent %s:", "wl"), $uc_singular),
      "edit_item" => sprintf(__("Edit %s", "wl"), $uc_singular),
      "update_item" => sprintf(__("Update %s", "wl"), $uc_singular),
      "add_new_item" => sprintf(__("Add new %s", "wl"), $uc_singular),
      "new_item_name" => sprintf(__("New %n Name", "wl"), $uc_singular),
      "menu_name" => $uc_plural
    );

    $options = array_merge(array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => $name["plural"])
      ), $options);

    register_taxonomy(
      $name["singular"],
      $post_types,
      $options
    );

  }
}

Wordless::register_helper("ModelHelper");
