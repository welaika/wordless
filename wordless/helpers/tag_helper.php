<?php
/**
 * TagHelper
 * 
 * Provides methods to generate HTML tags programmatically when you can't use a Builder.
 * @package Wordless
 */
class TagHelper {
  
  private function tag_options($options, $prefix = "") {

    $html_content = "";

    if (is_array($options)) {

      foreach ($options as $option_key => $option_value) {

        if (is_array($option_value)){
          if($option_key == "data"){
            $html_content .= $this->tag_options($option_value, $option_key . "-");
          } else {
            $html_content .= " " . $prefix . $option_key . "=" . "\"". addslashes(json_encode($option_value))  . "\"";  
          }
        } else {
          if (is_null($option_value) || (empty($option_value)) || ($option_value == $option_key)) {
            $html_content .= " " . $prefix . $option_key; 
          } else {
            $html_content .= " " . $prefix . $option_key . "=" . "\"". $option_value  . "\"";
          }
        }
      }
    } else {
      //We have only a simple string and not an array
      $html_content .= " " . $options;
    }

    return $html_content;
      
  }

  function content_tag($name, $content, $options = NULL, $escape = false) {

    if (is_null($content)){
      $html_content = "<" . $name;
      if(!is_null($options)){
        $html_content .= $this->tag_options($options);
      }
      $html_content .= "/>";
    } else {
      $html_content = "<" . $name;
      if(!is_null($options)){
        $html_content .= $this->tag_options($options);
      }
      $html_content .= ">";
      $html_content .= ((bool) $escape) ? addslashes($content) : $content;
      $html_content .= "</" . $name . ">";
    }

    return $html_content;

  }

  function include_stylesheet($url) {
    if (!preg_match("/^http:\/\//", $url)) {
      $url = stylesheet_url($url);
      if (!preg_match("/\.css$/", $url)) $url .= ".css";
    }

    $options = array( "href"  => $url,
                      "media" => "all",
                      "rel"   => "stylesheet",
                      "type"  => "text/css"
              );

    return $this->content_tag("link", NULL, $options);
  }

  function include_javascript($url) {
    if (!preg_match("/^http:\/\//", $url)) {
      $url = javascript_url($url);
      if (!preg_match("/.js$/", $url)) $url .= ".js";
    }

    $options = array( "src"  => $url,
                      "media" => "all",
                      "rel"   => "stylesheet",
                      "type"  => "text/javascript"
              );

    return $this->content_tag("script", "", $options);
  }

  function rss_link($title, $url) {
    
    $options = array( "href"  => $url,
                      "title" => $title,
                      "rel"   => "alternate",
                      "type"  => "application/rss+xml"
              );

    return $this->content_tag("link", NULL, $options);
  }

  function option_tag($text, $name, $value, $selected) {
    if (is_wp_error($value)) {
      return print_r($value);
    }

    $options = array( "name"  => $name,
                      "value" => $value
              );
    if ($selected) {
      $options["selected"] = "selected";
    }

    return $this->content_tag("option", $text, $options);
  }

  function link_to($text = '', $link = '', $attributes = NULL) {
    if (!is_string($text)) {
      $text = "Testo non disponibile";
    }
    if (!is_string($link)) {
      $link = "#link_not_available";
    }

    $options = array( "href"  => $link);
    if(is_array($attributes)){
      $options = array_merge($options, $attributes);
    } else {
      $options = array_merge($options, array($attributes => NULL));
    }

    return $this->content_tag("a", $text, $options);
  }

  function image_tag($img, $attributes = NULL) {
    if (!preg_match("/^(http|\/)/", $img)) {
      $img = public_url("images/$img");
    }

    $options = array( "src"  => $img );
    if(is_array($attributes)){
      $options = array_merge($options, $attributes);
    }

    return $this->content_tag("img", NULL, $options);
  }

  function active_if($active_check) {
    return $active_check ? "active" : "inactive";
  }

  function get_post_type_singular_name() {
    $obj = get_post_type_object(get_post_type());
    return $obj->labels->name;
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
}

Wordless::register_helper("TagHelper");
