<?php

function render_template($name) {
  $tmp_dir = Wordless::theme_temp_path();
  $template_path = Wordless::join_paths(Wordless::theme_views_path(), "$name.haml");

  if (!is_file($template_path)) {
    echo "<strong>Ouch!!</strong> It seems that <code>$template_path</code> doesn't exists!";
  } else if (is_writable($tmp_dir)) {
    $haml = new HamlParser(array('style' => 'expanded', 'ugly' => false/*, 'helperFile' => dirname(__FILE__).'/../ThemeHamlHelpers.php'*/));
    include $haml->parse($template_path, $tmp_dir);
  } else {
    echo "<strong>Ouch!!</strong> It seems that the <code>/tmp/</code> directory is not writable by the server! Go fix it!";
  }
}

function get_partial_content($name) {
  ob_start();
  render_partial($name);
  $partial_content = ob_get_contents();
  ob_end_clean();
  return $partial_content;
}

function render_partial($name) {
  $parts = preg_split("/\//", $name);
  if (!preg_match("/^_/", $parts[sizeof($parts)-1])) {
    $parts[sizeof($parts)-1] = "_" . $parts[sizeof($parts)-1];
  }
  render_template(implode($parts, "/"));
}

function render_view($name) {
  global $current_view;
  $current_view = $name;
  render_template("template");
}

