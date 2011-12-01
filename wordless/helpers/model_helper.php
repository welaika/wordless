<?php

class ModelHelper {

  function new_post_type($name, $supports = array("title", "editor")) {

    if (!is_array($name)) {
      $name = array(
        "singular" => $name,
        "plural" => pluralize($name)
      );
    }

    $uc_plural = __(ucwords(preg_replace("/_/", " ", $name["plural"])));
    $uc_singular = __(ucwords(preg_replace("/_/", " ", $name["singular"])));

    $labels = array(
      'name' => $uc_plural,
      'singular_name' => $uc_singular,
      'add_new_item' => sprintf(__("Add new %s", "we"), $uc_singular),
      'edit_item' => sprintf(__("Edit %s", "we"), $uc_singular),
      'new_item' => sprintf(__("New %s", "we"), $uc_singular),
      'view_item' => sprintf(__("View %s", "we"), $uc_singular),
      'search_items' => sprintf(__("Add new %s", "we"), $uc_plural),
      'not_found' => sprintf(__("No %s found.", "we"), $uc_plural),
      'not_found_in_trash' => sprintf(__("No %s found in Trash", "we"), $uc_plural),
      'parent_item_colon' => ',',
      'menu_name' => $uc_plural
    );

    register_post_type(
      $name["singular"],
      array(
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
        'supports' => $supports
      )
    );
  }

  function new_taxonomy($name, $post_types, $hierarchical = true) {

    if (!is_array($name)) {
      $name = array(
        "singular" => $name,
        "plural" => pluralize($name)
      );
    }

    $uc_plural = ucwords(preg_replace("/_/", " ", $name["plural"]));
    $uc_singular = ucwords(preg_replace("/_/", " ", $name["singular"]));

    $labels = array(
      "name" => $uc_singular,
      "singular_name" => $uc_singular,
      "search_items" => sprintf(__("Search %s", "we"), $uc_plural),
      "all_items" => sprintf(__("All %s", "we"), $uc_plural),
      "parent_item" => sprintf(__("Parent %s", "we"), $uc_singular),
      "parent_item_colon" => sprintf(__("Parent %s:", "we"), $uc_singular),
      "edit_item" => sprintf(__("Edit %s", "we"), $uc_singular),
      "update_item" => sprintf(__("Update %s", "we"), $uc_singular),
      "add_new_item" => sprintf(__("Add new %s", "we"), $uc_singular),
      "new_item_name" => sprintf(__("New %n Name", "we"), $uc_singular),
      "menu_name" => $uc_plural
    );

    register_taxonomy(
      $name["singular"],
      $post_types,
      array(
        'hierarchical' => $hierarchical,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => $name["plural"])
      )
    );

  }
}

Wordless::register_helper("ModelHelper");
