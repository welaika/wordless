<?php
/**
 * Provides methods to generate HTML tags programmatically when you can't use a Builder.
 *
 * @package Wordless
 *
 * @ingroup helperclass
 */
class TagHelper {

  private function tag_options($options, $prefix = "") {

    $attributes = array();
    $html_content = array();

    if (is_array($options)) {

      foreach ($options as $option_key => $option_value) {

        if (is_array($option_value)){
          if($option_key == "data"){
            $attributes[] = $this->tag_options($option_value, $option_key . "-");
          } else {
            $html_content[] = $prefix . $option_key . "=" . "\"". addslashes(json_encode($option_value)) . "\"";
          }
        } else {
          if (is_null($option_value) || (empty($option_value)) || ($option_value == $option_key)) {
            $html_content[] = $prefix . $option_key;
          } elseif(is_bool($option_value) && ($option_value == true)) {
            $html_content[] = $prefix . $option_key . "=" . "\"". $prefix . $option_key . "\"";
          } else {
            $html_content[] = $prefix . $option_key . "=" . "\"". $option_value . "\"";
          }
        }
      }
    } else {
      //We have only a simple string and not an array
      $html_content[] = $options;
    }

    return join(" ", $html_content);
  }

  /**
  * Awaiting for documentation
  *
  * @todo
  *   Loss of doc
  */
  function content_tag($name, $content, $options = NULL, $escape = false) {

    if (is_null($content)){
      $html_content = "<" . $name;
      if(!is_null($options) && $attrs = $this->tag_options($options)){
        $html_content .= " " . $attrs;
      }
      $html_content .= "/>";
    } else {
      $html_content = "<" . $name;
      if(!is_null($options) && $attrs = $this->tag_options($options)){
        $html_content .= " " . $attrs;
      }
      $html_content .= ">";
      $html_content .= ((bool) $escape) ? htmlentities($content) : $content;
      $html_content .= "</" . $name . ">";
    }

    return $html_content;
  }

  /**
  * Awaiting for documentation
  *
  * @todo
  *   Loss of doc
  */
  function option_tag($text, $name, $value, $selected = NULL) {
    $options = array(
      "name"  => $name,
      "value" => $value
    );

    if ($selected) {
      $options["selected"] = true;
    }

    return $this->content_tag("option", $text, $options);
  }

  /**
  * Awaiting for documentation
  *
  * @todo
  *   Loss of doc
  */
  function link_to($text = '', $link = NULL, $attributes = NULL) {
    if (!is_string($link)) {
      $link = "#";
    }

    $options = array("href" => $link);
    if (is_array($attributes)){
      $options = array_merge($options, $attributes);
    }

    return $this->content_tag("a", $text, $options);
  }

  /**
  * Awaiting for documentation
  *
  * @todo
  *   Loss of doc
  */
  function content_type_meta_tag($content = NULL) {

    $content = $content ? $content : get_bloginfo('html_type') . '; ' . 'charset=' . get_bloginfo('charset');

    $attrs = array(
      "http-equiv" => "Content-type",
      "content"    => $content
    );

    return content_tag("meta", NULL, $attrs);
  }

  /**
  * Awaiting for documentation
  *
  * @todo
  *   Loss of doc
  */
  function title_tag($title = NULL, $attributes = array()) {
    $title = $title ? $title : get_page_title();
    return content_tag("title", $title, $attributes);
  }

  /**
  * Awaiting for documentation
  *
  * @todo
  *   Loss of doc
  */
  function pingback_link_tag($url = NULL) {
    $url = $url ? $url : get_bloginfo('pingback_url');
    $attributes = array("href" => $url);
    return content_tag("link", NULL, $attributes);
  }

}

Wordless::register_helper("TagHelper");
