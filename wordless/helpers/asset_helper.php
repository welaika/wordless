<?php

function public_url($path) {
  return parse_url(get_bloginfo('stylesheet_directory'), PHP_URL_PATH) . "/public/$path";
}

function asset_url($path) {
  return public_url("assets/$path");
}

function stylesheet_url($path) {
  return asset_url("stylesheets/$path");
}

function javascript_url($path) {
  return asset_url("javascripts/$path");
}


