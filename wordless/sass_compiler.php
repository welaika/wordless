<?php

global $wp;

require_once Wordless::join_paths(dirname(dirname(__FILE__)), 'vendor/phamlp/sass/SassParser.php');

$file_path = Wordless::join_paths(Wordless::theme_stylesheets_path(), basename($wp->query_vars["sass_file_path"]) . ".sass");

header("Content-Type: text/css");

if (is_file($file_path)) {
  $sass = new SassParser(array(
    'style' => 'nested',
    'cache_location' => Wordless::theme_temp_path(),
    'extensions' => array('compass' => array())
  ));
  echo $sass->toCss($file_path);
} else {
  echo sprintf("body > *:first-child::before { content: 'File %s does not exists!'; font-family: monospace; };", $file_path);
}


