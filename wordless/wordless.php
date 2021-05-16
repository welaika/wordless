<?php

require_once Wordless::join_paths(dirname(__FILE__), '../vendor/autoload.php');
require_once Wordless::join_paths(dirname(__FILE__), 'theme_builder.php');
require_once Wordless::join_paths(dirname(__FILE__), 'wordless_test_configurations.php');
require_once Wordless::join_paths(dirname(__FILE__), 'wp-cli-wordless', 'command.php');


/**
 * Wordless holds all the plugin setup and initialization.
 */
class Wordless {

    private static $preprocessors = array();
    private static $preferences = array();
    private static $helpers = array();
    public static $webpack_files_names = array(
        // handler => path relative to theme root
        'procfile' => 'Procfile',
        'env' => '.env',
        'package' => 'package.json',
        'webpack' => 'webpack.config.js',
        'webpack.env' => 'webpack.env.js',
        'main' => 'src/main.js',
        'yarn' => 'yarn.lock',
        'nvmrc' => '.nvmrc',
        'stylelintignore' => '.stylelintignore',
        'stylelintrc' => '.stylelintrc.json',
        'release' => 'release.txt',
        'eslintrc' => '.eslintrc.json'
    );

    public static function initialize() {
        $missing_directories = Wordless::theme_is_wordless_compatible(true);
        if (empty($missing_directories)){
            self::load_i18n();
            self::require_helpers();
            self::require_theme_initializers();
            self::register_activation();
        } else {
            $dirlist = join(', ', array_map('basename', $missing_directories));
            $error_text = <<<"ERRORTEXT"

Missing directories: theme is missing following directories: $dirlist.
You could try following actions:

* ignore this WARNING if you're creating the starter theme
* `wp wordless theme create THEMENAME` if you have not created the starter theme yet
* manually fix the theme if you have deleted something and if you know what are you doing :)
* disable Wordless plugin if you activated it by error

ERRORTEXT;
            trigger_error($error_text, E_USER_WARNING);
        }

        self::register_plugin_i18n();
    }

    public static function helper($class_name) {
        if (!isset(self::$helpers[$class_name])) {
            self::$helpers[$class_name] = new $class_name();
        }
        return self::$helpers[$class_name];
    }

    public static function register_helper($class_name) {
        foreach (get_class_methods($class_name) as $method) {
            if (!function_exists($method)) {
                $global_function_definition = "function $method() { \$helper = Wordless::helper('$class_name'); \$args = func_get_args(); return call_user_func_array(array(\$helper, '$method'), \$args); }";
                eval($global_function_definition);
            }
        }
    }

    public static function register_activation() {
        register_activation_hook(__FILE__, array(__CLASS__, 'install') );
    }

    public static function install() {
        self::assets_rewrite_rules();
        flush_rewrite_rules();
    }

    public static function register_plugin_i18n() {
        add_action('init', array(__CLASS__, 'plugin_i18n'));
    }

  /**
   * Recursively searches inside a directory for specific files.
   *
   * * @param string $directory_path
   *   The path of the directory to search recursively
   * * @param string $pattern
   *   The glob pattern of the files (see http://php.net/manual/en/function.glob.php)
   * * @param int $flags
   *   The glob search flags (see http://php.net/manual/en/function.glob.php)
   *
   */
    public static function recursive_glob($path, $pattern = '*', $flags = 0) {
        $files = glob(self::join_paths($path, $pattern), $flags);

        if (!is_array($files)) {
            $files = array();
        }

        $paths = glob(self::join_paths($path, '*'), GLOB_ONLYDIR | GLOB_NOSORT);

        if (!empty($paths)) {
            foreach ($paths as $sub_path) {
                $subfiles = self::recursive_glob($sub_path, $pattern, $flags);
                if (is_array($subfiles)) {
                    $files = array_merge($files, $subfiles);
                }
            }
        }

        return $files;
    }

  /**
   * Set a Wordless preference
   */
    public static function set_preference($name, $value) {
        self::$preferences[$name] = $value;
    }

  /**
   * Get a Wordless preference
   */
    public static function preference($name, $default = '') {
        return isset(self::$preferences[$name]) ? self::$preferences[$name] : $default;
    }

    public static function load_i18n() {
        $theme_locales_path = self::theme_locales_path();
        if (file_exists($theme_locales_path) && is_dir($theme_locales_path)) {
          load_theme_textdomain('wl', $theme_locales_path);
      }
    }

    public static function plugin_i18n() {
        $plugin_locales_rel_path = self::join_paths('wordless', 'locales');
        load_plugin_textdomain('wl', false, $plugin_locales_rel_path);
    }

    public static function require_helpers() {
        require_once Wordless::join_paths(dirname(__FILE__), "helpers.php");
        $helpers_path = self::theme_helpers_path();
        self::require_once_dir($helpers_path);
    }

    public static function require_theme_initializers() {
        $initializers_path = self::theme_initializers_path();
        self::require_once_dir($initializers_path);
    }

  /**
   * Require one directory
   * @param string $path
   */
    public static function require_once_dir($path) {
        $list_files = glob(Wordless::join_paths($path, "*.php"));
        if (is_array($list_files)) {
            foreach ($list_files as $filename) {
                require_once $filename;
            }
        }
    }

  /**
   * Checks if required directories exist. If any are missing, it will return false.
   * If passed `true` as argument, this function will return an array of missing directories.
   *
   * * @param boolean $return_array
   *   Set true to get an array of missing directories
   *
   */
    public static function theme_is_wordless_compatible($return_array = false) {
        $missing = self::get_theme_missing_directories();

        if(!empty($missing)){
            return ($return_array) ? $missing : false;
        }

        return ($return_array) ? array() : true;
    }

  /**
  * Return directories missing. Empty array if nothing is missing.
  */
    public static function get_theme_missing_directories(){
        $missing = array();

        // Scan required directories.
        $required_directories = array(
            self::theme_helpers_path(),
            self::theme_initializers_path(),
            self::theme_locales_path(),
            self::theme_views_path(),
            self::theme_assets_path(),
            self::theme_static_assets_path(),
            self::theme_stylesheets_path(),
            self::theme_javascripts_path(),
            self::theme_temp_path()
        );

        foreach ($required_directories as $dir) {
            if (!file_exists($dir) || !is_dir($dir)) {
                $missing[] = $dir;
            }
        }

        return $missing;
    }

    /**
     * Tells if the theme is potentially automatically upgradable.
     *
     * A theme is considered upgradable based on 2 condition:
     * 1. Folder structure is standard and corresponds to the new Wordless version's one
     * 2. Theme has a `package.json` and a Procfile.
     *
     * @return boolean
     */
    public static function theme_is_upgradable() : bool {
        if (self::theme_is_wordless_compatible() === false)
            return false;

        $missing = array();

        $required_files = array(
            self::theme_procfile_path(),
            self::theme_packagejson_path()
        );

        foreach ($required_files as $file) {
            if (!file_exists($file)) {
                $missing[] = $file;
            }
        }

        if(empty($missing)) {
            return true;
        } else {
            return false;
        }
    }

    public static function clear_theme_temp_path() {
        $files = self::recursive_glob(self::theme_temp_path());

        foreach($files as $file){
            if(is_file($file))
                unlink($file);
        }
    }

    public static function theme_path() {
        return get_template_directory();
    }

    public static function theme_helpers_path() {
        return self::join_paths(self::theme_path(), 'helpers');
    }

    public static function theme_initializers_path() {
        return self::join_paths(self::theme_path(), 'config/initializers');
    }

    public static function theme_locales_path() {
        return self::join_paths(self::theme_path(), 'config/locales');
    }

    public static function theme_views_path() {
        return self::join_paths(self::theme_path(), 'views');
    }

    public static function theme_assets_path() {
        return self::join_paths(self::theme_path(), 'src');
    }

    public static function theme_stylesheets_path() {
        return self::join_paths(self::theme_path(), 'src/stylesheets');
    }

    public static function theme_javascripts_path() {
        return self::join_paths(self::theme_path(), 'src/javascripts');
    }

    public static function theme_static_assets_path() {
        return self::join_paths(self::theme_path(), 'dist');
    }

    public static function theme_static_javascripts_path() {
        return self::join_paths(self::theme_path(), 'dist/javascripts');
    }

    public static function theme_temp_path() {
        return self::preference("theme.temp_dir", self::join_paths(self::theme_path(), 'tmp'));
    }

    public static function theme_url() {
        return parse_url(get_bloginfo('template_url'), PHP_URL_PATH);
    }

    public static function theme_procfile_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['procfile']);
    }

    public static function theme_dotenv_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['env']);
    }

    public static function theme_webpackconfig_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['webpack']);
    }

    public static function theme_webpackenv_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['webpack.env']);
    }

    public static function theme_packagejson_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['package']);
    }

    public static function theme_webpackentrypoint_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['main']);
    }

    public static function theme_yarndotlock_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['yarn']);
    }

    public static function theme_nvmrc_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['nvmrc']);
    }

    public static function theme_stylelintignore_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['stylelintignore']);
    }

    public static function theme_stylelintrc_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['stylelintrc']);
    }

    public static function theme_release_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['release']);
    }

    public static function theme_eslintrc_path() {
        return self::join_paths(self::theme_path(), self::$webpack_files_names['eslintrc']);
    }

    public static function join_paths() {
        $args = func_get_args();
        $paths = array();

        foreach($args as $arg) {
          $paths = array_merge($paths, (array)$arg);
        }

        foreach($paths as &$path) {
            $path = trim($path, '/');
        }

        if (substr($args[0], 0, 1) == '/') {
            $paths[0] = '/' . $paths[0];
        }

        return join('/', $paths);
    }

    /**
     * This function renders (as expected by standard) the cache management page accessible from the admin menu.
     * In this page you can invalidate (delete) the current static cached files
     */
    public static function render_static_cache_menu() {
        $notice = '';

        if ( isset($_POST['clear-all-cache']) && 'all' === $_POST['clear-all-cache'] ) {
            self::clear_theme_temp_path();
            $cached_files = self::recursive_glob(self::theme_temp_path());

            if (count($cached_files) > 0) {
                $notice_class = 'notice-error';
                $notice_message = __('Impossibile eliminare tutta la cache all\'interno della cartella temporanea');
            } else {
                $notice_class = 'notice-success';
                $notice_message = __('Cache eliminata correttamente');
            }

            $notice = "
                <div class='notice $notice_class is-dismissible' style='margin-left: 2px;'>
                    <p>$notice_message</p>
                </div>";
        }

        if ( isset($_POST['clear-single-cache']) && !empty($_POST['clear-single-cache']) ) {
        $path = self::join_paths(self::theme_temp_path(), $_POST['clear-single-cache']);
        unlink($path);
        $cached_files = self::recursive_glob(self::theme_temp_path());

        if (in_array($path, $cached_files)) {
            $notice_class = 'notice-error';
            $notice_message = __('Impossibile eliminare la cache della singola pagina selezionata');
        } else {
            $notice_class = 'notice-success';
            $notice_message = __('Cache della singola pagina eliminata correttamente');
        }

        $notice = "
            <div class='notice $notice_class is-dismissible' style='margin-left: 2px;'>
                <p>$notice_message</p>
            </div>";
        }

        if ( !isset($_POST['clear-single-cache']) && !isset($_POST['clear-all-cache']) ) {
            $cached_files = self::recursive_glob(self::theme_temp_path());
        }

        echo "
        $notice
        <wrap>
            <h2>" . __('Gestione della cache') . "</h2>
            <p>". __('In questa sezione è possibile eliminare la cache dalle pagine. Verranno rigenerate automaticamente durante la navigazione.') . "</p>
            <form method='POST'>
                <input type='hidden' name='clear-all-cache' value='all' />
                <input type='submit' class='button-primary button' value='" . __('Invalida tutta la cache') . "' />
            </form>

            <br/>

            <h2>" . __('Pagine attualmente in cache') . "</h2>
            <table id='cached_list'>";

        if (count($cached_files) == 0) {
        echo "
            <tr>
            <td colspan=3 class='cached_list__file__empty'>Nessun file nella cache</td>
            </tr>
        ";
        }

        foreach($cached_files as $static) {
            $file_parts = explode('/', $static);
            $file = end($file_parts);
            if (substr($file, -5) == '.html') {
            $line = fgets(fopen($static, 'r'));
            preg_match('~<title>([^{]*)</title>~i', $line, $match);
            $title = isset($match[1]) && !empty($match[1]) ? $match[1] : '<i>-- Nessun titolo --</i>';
            echo "
                <tr>
                <td class='cached_list__file__title'>$title</td>
                <td class='cached_list__file__path'>$file</td>
                <td class='cached_list__file__action'>
                    <form method='POST'>
                    <input type='hidden' name='clear-single-cache' value='$file' />
                    <button type='submit'>
                        <span class='dashicons dashicons-remove'></span>
                    </button>
                    </form>
                </td>
                </tr>
            ";
            }
        }

        echo "
            </table>
        ";

        echo "
        </wrap>

        <style>
        #cached_list {
            border-collapse: collapse;
            width: 50%;
        }

        #cached_list td:not(.cached_list__file__empty) {
            border-bottom: 1px solid lightgrey;
            padding: 10px 5px;
        }

        .cached_list__file__empty {
            font-style: italic;
        }

        .cached_list__file__title {
            font-size: 14px;
            font-weight: 700;
        }

        .cached_list__file__path {
            font-size: 12px;
        }

        .cached_list__file__action {
            text-align: right;
        }

        #cached_list button {
            border: none;
        }

        #cached_list button:hover {
            cursor: pointer;
        }

        #cached_list button:focus {
            outline: none;
        }
        </style>
        ";
    }

}
