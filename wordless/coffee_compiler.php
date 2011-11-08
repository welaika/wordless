<?php

global $wp;

require 'assets/coffee_compiler.php';

$file_path = Wordless::join_paths(Wordless::theme_javascripts_path(), basename($wp->query_vars["coffee_file_path"]) . ".coffee");

header("Content-Type: text/javascript");

if (is_file($file_path)) {
  $compiler = new CoffeeCompiler('/usr/local/bin/coffee', '/usr/local/bin/node');
  echo $compiler->filter($file_path, Wordless::theme_temp_path());
} else {
  echo sprintf("body > *:first-child::before { content: 'File %s does not exists!'; font-family: monospace; };", $file_path);
}



