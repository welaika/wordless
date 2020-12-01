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
    * @param boolean $static If `true` static rendering of PUG templates will
    *                        be activated.
    *
    */
    function render_template($name, $locals = array(), $static = false) {
        $valid_filenames = array(
            "$name.html.pug",
            "$name.pug",
            "$name.html.php",
            "$name.php",
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
          render_error("Template missing", "<strong>Ouch!!</strong> It seems that <code>$name.html.pug</code> or <code>$name.html.php</code> doesn't exist!");
        }

        $tmp_dir = Wordless::theme_temp_path();

        // REALLY IMPORTANT NOTE: the cache policy of static generated views is based on the
        // view's name + the SHA1 of serialized $locals. As it stands the best way
        // to introduce business logic in the expiration logic is to pass ad hoc extra variables
        // into the $locals array. For example having
        //     render_template('pages/photos', $locals = [ 'cache_key' => customAlgorithm() ], $static = true)
        // when `customAlgorithm()` will change, it will automatically invalidate the static cache for this
        // template
        $staticPath = Wordless::join_paths(
            $tmp_dir,
            basename($name) . '.' . sha1(serialize($locals)) . '.html'
        );

        switch ($format) {
            case 'pug':
                require_once('pug/wordless_pug_options.php');

                if ($this->ensure_dir($tmp_dir)) {
                    // Read the environment from various sources. Note that .env file has precedence
                    if ( getenv('ENVIRONMENT') ) {
                        $env = getenv('ENVIRONMENT');
                    } elseif ( defined('ENVIRONMENT') ) {
                        $env = ENVIRONMENT;
                    } else {
                        $env = 'development';
                    }

                    // Read the option to bypass static cache from various sources. Note that .env file has precedence
                    if ( getenv('BYPASS_STATIC') ) {
                        $bypass_static = getenv('BYPASS_STATIC'); // getenv() returns a string
                    } elseif ( defined('BYPASS_STATIC') ) {
                        $bypass_static = var_export(BYPASS_STATIC, true); // constant could be a boolean so we uniform to a string representation
                    } else {
                        $bypass_static = 'false'; // default value
                    }

                    if ( in_array( $env, array('staging', 'production') ) ) {
                        if (true === $static && 'false' == $bypass_static) {
                            if (file_exists($staticPath)) {
                                include $staticPath;
                            } else {
                                \Phug\Optimizer::call('renderAndWriteFile', [$template_path, $staticPath, $locals], WordlessPugOptions::get_options());
                                include $staticPath;
                            }
                        } else {
                            \Phug\Optimizer::call(
                                'displayFile', [$template_path, $locals], WordlessPugOptions::get_options()
                            );
                        }
                    } else {
                        $pug = new \Phug\Renderer(WordlessPugOptions::get_options());
                        if (true === $static && 'false' == $bypass_static) {
                            if (file_exists($staticPath)) {
                                include $staticPath;
                            } else {
                                $res = $pug->renderAndWriteFile($template_path, $staticPath, $locals);
                                include $staticPath;
                            }
                        } else {
                            $pug->displayFile($template_path, $locals);
                        }
                    }
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
     * Wraps render_template() function activating the static rendering strategy
     *
     * @param string $name Template path relative to +views+ directory
     * @param array $locals Associative array of variable that will be scoped into the template
     * @return void
     */
    function render_static($name, $locals = array()) {
        $fileInfo = new SplFileInfo($name);
        $extension = $fileInfo->getExtension();
        if ('pug' !== $extension) {
            render_error("Static rendering only available for PUG templates", "<strong>Ouch!!</strong> It seems you required a <code>render_static</code> for a PHP template, but this render method is supported only for PUG. Use <code>render_partial</code> or <code>render_template</code> instead.");
        }

        render_template($name, $locals = array(), $static = true);
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
    function render_partial($name, $locals = array(), $static = false) {
        $parts = preg_split("/\//", $name);
        if (!preg_match("/^_/", $parts[sizeof($parts)-1])) {
            $parts[sizeof($parts)-1] = "_" . $parts[sizeof($parts)-1];
        }
        render_template(implode($parts, "/"), $locals, $static);
    }

    /**
    * Renders a view. Views are rendered based on the routing.
    *   They will show a template and a yielded content based
    *   on the user requested page.
    *
    * @param  string $name   Filename with path relative to theme/views
    * @param  array  $locals An associative array. Keys will be variables'
    *                        names and values will be variable values inside
    *                        the view
    *
    * @deprecated 5.0
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
