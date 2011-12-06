<?php
/**
 * AssetTagHelper
 *
 * This module provides methods for generating HTML that links views to assets such
 * as images, javascripts, stylesheets, and feeds. These methods do not verify
 * the assets exist before linking to them
 * @package Wordless
 */
class AssetTagHelper {

  private function get_feed($type, $model) {
    if (($model=="posts") || ($model=="post"))  {

        switch ($type) {
          case "rdf":
            return bloginfo('rdf_url');
          case "rss1":
          case "rss092":
            return bloginfo('rss_url');
          case "atom":
            return bloginfo('atom_url');
          case "rss":
          case "rss2":
          default:
            return bloginfo('rss2_url');
        }

    } elseif (($model=="comments") || ($model=="comment")) {

      return bloginfo('comments_rss2_url');

    } else {

      return NULL;
    }

  }

  function auto_discovery_link_tag($type = "rss", $url_options = NULL, $tag_options = NULL) {

    $options = array();

    switch ($type) {
      case "rss":
        $options['type'] = "application/rss+xml";
        break;
      case "atom":
        $options['type'] = "application/atom+xml";
        break;
      default:
        $type = "rss";
        $options['type'] = "application/rss+xml";
        break;
    }

    switch ($url_options) {
      case (is_string($url_options)):
        $options['href'] = $url_options;
      case is_array($url_options):
        if (isset($url_options['feed'])) {
          $options['href'] = $this->get_feed($type, $url_options['feed']);
        } elseif (isset($url_options['href'])) {
          $options['href'] = $url_options['href'];
        } else {
          $options['href'] = "#";
        }
      case is_null($url_options):
      default:
        $options['href'] = $this->get_feed($type, "posts");
    }

    if(is_array($tag_options)){
      $options = array_merge($options, $tag_options);
    }

    return content_tag("link", NULL, $options);
  }

  function favicon_link_tag($source= "/favicon.ico", $attributes = array()) {

    $options = array( "rel"  => 'shortcut icon',
                      "href" => $source,
                      "type" => 'image/vnd.microsoft.icon');

    $options = array_merge($options, $attributes);

    return content_tag("link", NULL, $options);

  }

  function image_tag($source, $attributes = NULL) {
    if (!preg_match("/^(http|\/)/", $img)) {
      $img = image_url($img);
    }

    $options = array( "src"  => $source );
    if(is_array($attributes)){

      /*if((isset($attributes['size']) && (preg_match('/^([0-9]+)(x([0-9]+))?(px)?$/',$attributes['size']))){
        list($options["width"], $options["height"]) = split("x", $attributes['size']);
        unset($attributes['size']);
      }*/

      $options = array_merge($options, $attributes);
    }

    return content_tag("img", NULL, $options);
  }

  function video_tag($sources, $attributes = array()){

    if(is_array($sources)) {
      $html_content = "";

      foreach($sources as $source) {
        if(is_string($source)){
          $html_content .=  content_tag("source", NULL, array("src" => $source));
        } else {
          $html_content .=  content_tag("source", NULL, $source);
        }
      }

      return content_tag("video", $html_content, $attributes);

    } else {

      $options = array_merge(array("src" => $sources), $attributes);
      return content_tag("video", NULL, $options);

    }

    function audio_tag($source, $attributes = array()){

      $options = array_merge(array("src" => $source), $attributes);

      return content_tag("audio", NULL, $options);

    }
  }
}

Wordless::register_helper("AssetTagHelper");
