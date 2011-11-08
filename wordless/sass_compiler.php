<?php

global $wp;

require 'assets/compass_compiler.php';

$file_path = Wordless::join_paths(Wordless::theme_stylesheets_path(), basename($wp->query_vars["sass_file_path"]) . ".sass");

header("Content-Type: text/css");

if (is_file($file_path)) {
  $compiler = new CompassCompiler('compass', Wordless::theme_temp_path());
  echo $compiler->filter($file_path, Wordless::theme_temp_path());
} else {
  echo sprintf("body > *:first-child::before { content: 'File %s does not exists!'; font-family: monospace; };", $file_path);
}


