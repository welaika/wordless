<?php

function include_stylesheet($url) {
  if (!preg_match("/\.css$/", $url)) $url .= ".css";
  if (!preg_match("/^http:\/\//", $url)) $url = asset_url($url);
  return '<link href="' . $url . '" media="all" rel="stylesheet" type="text/css" />';
}

function include_javascript($url) {
  if (!preg_match("/^http:\/\//", $url)) {
    $url = asset_url($url);
    if (!preg_match("/.js$/", $url)) $url .= ".js";
  }
  return '<script src="' . $url . '" type="text/javascript"></script>';
}

function rss_link($title, $url) {
  return '<link href="' . $url . '" rel="alternate" title="' . $title . '" type="application/rss+xml" />';
}

function option_tag($text, $name, $value, $selected) {
  if (is_wp_error($value)) {
    return print_r($value);
  }
  return "<option name='$name' value='$value' " . ($selected ? "selected='selected'" : "") . ">$text</option>";
}

function link_to($text = '', $link = '', $class = '') {
  if (!is_string($text)) {
    $text = "Testo non disponibile";
  }
  if (!is_string($link)) {
    $link = "#link_not_available";
  }
  if (is_array($class)) {
    $attributes = array();
    foreach ($class as $attribute => $value) {
      $attributes[] = "$attribute = '$value'";
    }
    $class = join(" ", $attributes);
  } else {
    $class = " class='$class'";
  }
  return "<a href='$link'$class>$text</a>";
}

function image_tag($img) {
  if (!preg_match("/^http/", $img)) {
    $img = public_url("images/$img");
  }
  return "<img src='$img' alt=''/>";
}

function active_if($active_check) {
  return $active_check ? "active" : "inactive";
}

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
    $title = "Ricerca";
  }
  if (is_front_page()) {
    return $prefix;
  }
  return "$prefix$separator$title";
}

