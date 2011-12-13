<?php

class RenderHelper {
  function render_error($title, $description) {
    ob_end_clean();
    require "templates/error_template.php";
    die();
  }

  function render_template($name) {
    $tmp_dir = Wordless::theme_temp_path();
    $template_path = Wordless::join_paths(Wordless::theme_views_path(), "$name.html.haml");
    $template_path = is_file($template_path) ? $template_path : Wordless::join_paths(Wordless::theme_views_path(), "$name.haml");

    if (!is_file($template_path)) {
      render_error("Template missing", "<strong>Ouch!!</strong> It seems that <code>$template_path</code> doesn't exist!");
    }

    if (!file_exists($tmp_dir)) {
      mkdir($tmp_dir, 0760);
    }

    if (!is_writable($tmp_dir)) {
      chmod($tmp_dir, 0760);
    }

    if (is_writable($tmp_dir)) {
      $haml = new HamlParser(array('style' => 'expanded', 'ugly' => false/*, 'helperFile' => dirname(__FILE__).'/../ThemeHamlHelpers.php'*/));
      include $haml->parse($template_path, $tmp_dir);
    } else {
      render_error("Temp dir not writable", "<strong>Ouch!!</strong> It seems that the <code>$tmp_dir</code> directory is not writable by the server! Go fix it!");
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

  function yield() {
    global $current_view;
    render_template($current_view);
  }

  function render_view($name, $layout = 'default') {
    ob_start();
    global $current_view;
    $current_view = $name;
    render_template("layouts/$layout");
    ob_flush();
  }
}

Wordless::register_helper("RenderHelper");
