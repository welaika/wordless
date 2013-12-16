<?php

  /**
  * Handles rendering of views, templates, partials
  *
  * @ingroup helperclass
  */
class RenderHelper {

  /**
   * Renders a preformatted error display view than dies
   *
   * @param  string $title       A title for the error
   * @param  string $description An explanation about the error
   */
  function render_error($title, $description) {
    ob_end_clean();
    require "templates/error_template.php";
    die();
  }

  /**
   * Renders a template and its contained plartials. Accepts
   * a list of locals variables which will be available inside
   * the code of the template
   *
   * @param  string $name   The template filenames (those not starting
   *                        with an underscore by convention)
   *
   * @param  array  $locals An associative array. Keys will be variables'
   *                        names and values will be variable values inside
   *                        the template
   *
   * @see php.bet/extract
   *
   */
  function render_template($name, $locals = array()) {
    $valid_filenames = array("$name.html.haml", "$name.haml", "$name.html.php", "$name.php");
    foreach ($valid_filenames as $filename) {
      $path = Wordless::join_paths(Wordless::theme_views_path(), $filename);
      if (is_file($path)) {
        $template_path = $path;
        $arr = explode('.', $path);
        $format = array_pop($arr);
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

  /**
  * This is awaiting for documentation
  *
  * @todo
  *   Loss of doc
  */
  function get_partial_content($name, $locals = array()) {
    ob_start();
    render_partial($name, $locals);
    $partial_content = ob_get_contents();
    ob_end_clean();
    return $partial_content;
  }

  /**
   * Renders a partial: those views followed by an underscore
   *   by convention. Partials are inside theme/views.
   *
   * @param  string $name   The template filenames (those not starting
   *                        with an underscore by convention)
   *
   * @param  array  $locals An associative array. Keys will be variables'
   *                        names and values will be variable values inside
   *                        the partial
   */
  function render_partial($name, $locals = array()) {
    $parts = preg_split("/\//", $name);
    if (!preg_match("/^_/", $parts[sizeof($parts)-1])) {
      $parts[sizeof($parts)-1] = "_" . $parts[sizeof($parts)-1];
    }
    render_template(implode($parts, "/"), $locals);
  }

  /**
  * Yield is almost inside every good templates. Based on the
  *   rendering view yield() will insert inside the template the
  *   specific required content (usually called partials)
  *
  * @see render_view()
  * @see render_template()
  */
  function wl_yield() {
    global $current_view, $current_locals;
    render_template($current_view, $current_locals);
  }

  /**
   * Renders a view. Views are rendered based on the routing.
   *   They will show a template and a yielded content based
   *   on the user requested page.
   *
   * @param  string $name   Filename with path relative to theme/views
   * @param  string $layout The template to use to render the view
   * @param  array  $locals An associative array. Keys will be variables'
   *                        names and values will be variable values inside
   *                        the view
   */
  function render_view($name, $layout = 'default', $locals = array()) {
    ob_start();
    global $current_view, $current_locals;

    $current_view = $name;
    $current_locals = $locals;

    render_template("layouts/$layout", $locals);
    ob_flush();
  }
}

Wordless::register_helper("RenderHelper");
