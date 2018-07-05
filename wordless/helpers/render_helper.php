<?php
use Pug\Pug;

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
        $valid_filenames = array(
            "$name.html.pug",
            "$name.pug",
            "$name.html.php",
            "$name.php",
            "$name.html.haml",
            "$name.haml"
        );

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
          render_error("Template missing", "<strong>Ouch!!</strong> It seems that <code>$name.html.pug</code> or <code>$name.html.haml</code> or <code>$name.html.php</code> doesn't exist!");
        }

        $tmp_dir = Wordless::theme_temp_path();

        switch ($format) {
            case 'haml':
                extract($locals);

                if ($this->ensure_dir($tmp_dir)) {
                    $haml = new HamlParser(array('style' => 'expanded', 'ugly' => false));
                    include $haml->parse($template_path, $tmp_dir);
                } else {
                    render_error("Temp dir not writable", "<strong>Ouch!!</strong> It seems that the <code>$tmp_dir</code> directory is not writable by the server! Go fix it!");
                }

                break;

            case 'pug':
                require_once('pug/wordless_pug_options.php');

                if ($this->ensure_dir($tmp_dir)) {
                    $pug = new Pug(WordlessPugOptions::get_options());

                    $pug->displayFile($template_path, $locals);
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
    * Retrievs contents of partial without printing theme
    * @param string $name The template filenames (those not starting
    *                        with an underscore by convention)
    *
    * @param  array  $locals An associative array. Keys will be variables'
    *                        names and values will be variable values inside
    *                        the partial
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
    * @param  string $name   The partial filenames (those starting
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

    private function ensure_dir($dir) {

        if (!file_exists($dir)) {
            mkdir($dir, 0770);
        }

        if (!is_writable($dir)) {
            chmod($dir, 0770);
        }

        if (is_writable($dir)) {
            return true;
        } else {
            return false;
        }
    }

}

Wordless::register_helper("RenderHelper");
