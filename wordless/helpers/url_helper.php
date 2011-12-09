<?php

class UrlHelper {
  function asset_url($path) {
    return parse_url(get_bloginfo('stylesheet_directory'), PHP_URL_PATH) . "/assets/$path";
  }

  function image_url($path) {
    return asset_url("images/$path");
  }

  function stylesheet_url($path) {
    return asset_url("stylesheets/$path");
  }

  function javascript_url($path) {
    return asset_url("javascripts/$path");
  }
}

Wordless::register_helper("UrlHelper");
