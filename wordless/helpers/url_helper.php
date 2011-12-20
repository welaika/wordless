<?php

class UrlHelper {
  function asset_url($path) {
    return get_bloginfo('stylesheet_directory') . "/assets/$path";
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
