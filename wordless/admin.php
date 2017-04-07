<?php

require_once "theme_builder.php";

/**
 * Wordless Admin
 **/
class WordlessAdmin
{

    public static function initialize() {
        self::load_menu();
    }

    public static function load_menu() {
        add_action('init', array('WordlessAdmin', 'check_roles'));
    }

    public static function check_roles() {
        if (current_user_can('edit_theme_options')) {
            if (!Wordless::theme_is_wordless_compatible()) {
                // Display a notice if options.php isn't present in the theme
                add_action('admin_notices', array('WordlessAdmin', 'add_notice'));
            }

            // If the user can edit theme options, let the fun begin!
            add_action('admin_menu', array('WordlessAdmin', 'add_page'), 1);
        }
    }

    public static function add_notice() {
        // Get a list of missing directories
        $dirs_missing = Wordless::theme_is_wordless_compatible(true);

        echo '<div class="error"><p>';
        echo sprintf(
          __('Your current theme does not seem to be a Wordless-compatible theme! <a href="%2$s" target="_blank">%1$s</a> (or <a href="%4$s" target="_blank">%3$s</a>)', "wl"),
          __('Create a new Wordless theme', "wl"),
          admin_url('admin.php?page=wordless'),
          __('learn more about Wordless', "wl"),
          'https://github.com/welaika/wordless#readme'
          );
        echo "</p>";
        echo "<p><strong>Error found:</strong></p>";
        echo "<ul>";

        foreach($dirs_missing as $dir){
            echo "<li>Missing Directory: ".$dir."</li>";
        }

        echo "</ul>";
        echo "</div>";
    }

    public static function add_page() {
        // add Wordless menu
        add_menu_page(
            'Wordless',
            'Wordless',
            'edit_theme_options',
            'wordless',
            array('WordlessAdmin', 'page_content'),
            plugins_url() . '/wordless/welaika.16x16.png',
            59
        );
    }

    public static function page_content() {
        $theme_options = array(
            "theme_name" => array(
                "label" => __("Theme Name", "wl"),
                "description" => __("This will be the name displayed inside WordPress.", "wl"),
                "default_value" => "Wordless"
            ),
            "theme_path" => array(
                "label" => __("Theme Directory", "wl"),
                "description" => __("Specify the <code>wp-content/themes</code> subdirectory name for this theme.", "wl"),
                "default_value" => "wordless"
            ),
            "chmod_set" => array(
                "label" => __("Permissions", "wl"),
                "description" => __("Specify three octal number components specifying access restrictions", "wl"),
                "default_value" => "0664"
            )
        );

        // Will read this from the required view admin_form.php
        $theme_is_upgradable = self::theme_is_upgradable();

        if ( "POST" == $_SERVER['REQUEST_METHOD'] && isset($_GET['update-options']) ) {
            // Form validation
            $valid = true;
            foreach ($theme_options as $name => $properties) {
                $value = $_POST[$name];
                if (empty($value)) {
                    $theme_options[$name]['error'] = __("This field is required!", "wl");
                    $valid = false;
                }
            }

            if ($valid) {
                $builder = new WordlessThemeBuilder($_POST["theme_name"], $_POST["theme_path"], intval($_POST["chmod_set"], 8));
                $builder->build();
                $builder->set_as_current_theme();
                require 'admin/admin_success_options_updated.php';
                die();
            }
        }

        if ( "POST" == $_SERVER['REQUEST_METHOD'] && isset($_GET['upgrade-theme']) ) {
            $builder = new WordlessThemeBuilder(null, null, intval(664, 8));
            $builder->upgrade_to_webpack();
            require 'admin/admin_success_theme_upgraded.php';
            die();
        }

        // Just render the page
        require 'admin/admin_form.php';
    }

    public static function theme_is_upgradable() {
        if (Wordless::theme_is_webpack_ready())
            return false;
        else
            return true;
    }
}

