<?php

class RenderHelper {
  function render_error($title, $description) {
    ob_end_clean();
    require "templates/error_template.php";
    die();
  }

  function render_template($name, $locals = array()) {
    $valid_filenames = array("$name.html.haml", "$name.haml", "$name.html.php", "$name.php");
    foreach ($valid_filenames as $filename) {
      $path = Wordless::join_paths(Wordless::theme_views_path(), $filename);
      if (is_file($path)) {
        $template_path = $path;
        $format = array_pop(explode('.', $path));
        break;
      }
    }

    if (!isset($template_path)) {
      render_error("Template missing", "<strong>Ouch!!</strong> It seems that <code>$name.html.haml</code> or <code>$name.html.php</code> doesn't exist!");
    }

    extract($locals);

    switch ($format) {
      case 'haml':
        $tmp_dir = Wordless::theme_temp_path();

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
        break;
      case 'php':
        include $template_path;
        break;
    }

  }

  function get_partial_content($name, $locals = array()) {
    ob_start();
    render_partial($name, $locals);
    $partial_content = ob_get_contents();
    ob_end_clean();
    return $partial_content;
  }

  function render_partial($name, $locals = array()) {
    $parts = preg_split("/\//", $name);
    if (!preg_match("/^_/", $parts[sizeof($parts)-1])) {
      $parts[sizeof($parts)-1] = "_" . $parts[sizeof($parts)-1];
    }
    render_template(implode($parts, "/"), $locals);
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
