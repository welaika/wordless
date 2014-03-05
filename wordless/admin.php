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
      __('Your current theme does seem to be a Wordless-compatible theme! <a href="%2$s" target="_blank">%1$s</a> (or <a href="%4$s" target="_blank">%3$s</a>)', "we"),
      __('Create a new Wordless theme', "we"),
      admin_url('themes.php?page=create_wordless_theme'),
      __('learn more about Wordless', "we"),
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

    // add Wordless perferences submenu
    $page = add_submenu_page(
      'wordless',
      'Setting Wordless preferences',
      __('Preferences', "we"),
      'edit_theme_options',
      'wordless_preferences',
      array('WordlessAdmin', 'preferences_content')
    );

    //Make the New Theme the first submenu item and the item to appear when clicking the parent.
    global $submenu;
    $submenu['wordless'][0][0] = __('New Theme', "we");
  }

  public static function page_content() {
    $theme_options = array(
      "theme_name" => array(
        "label" => __("Theme Name", "we"),
        "description" => __("This will be the name displayed inside WordPress.", "we"),
        "default_value" => "Wordless"
      ),
      "theme_path" => array(
        "label" => __("Theme Directory", "we"),
        "description" => __("Specify the <code>wp-content/themes</code> subdirectory name for this theme.", "we"),
        "default_value" => "wordless"
      ),
      "chmod_set" => array(
        "label" => __("Permissions", "we"),
        "description" => __("Specify three octal number components specifying access restrictions", "we"),
        "default_value" => "0664"
      )
    );
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      // Form validation
      $valid = true;
      foreach ($theme_options as $name => $properties) {
        $value = $_POST[$name];
        if (empty($value)) {
          $theme_options[$name]['error'] = __("This field is required!", "we");
          $valid = false;
        }
      }

      if ($valid) {
        $builder = new WordlessThemeBuilder($_POST["theme_name"], $_POST["theme_path"], intval($_POST["chmod_set"], 8));
        $builder->build();
        $builder->set_as_current_theme();
        require 'admin/admin_success.php';
        die();
      }
    }

    // Just render the page
    require 'admin/admin_form.php';
  }

    public static function preferences_content() {
    
    $wordless_preferences = array(
      "assets_preprocessors" => array(
        "label" => __("Preprocessors", "we"),
        "description" => __("List Preprocessors you need comma separeted (ex: if you use Less replace CompassPreprocessor with LessPreprocessor).", "we"),
        "default_value" => "SprocketsPreprocessor, CompassPreprocessor"
      ),
      "assets_cache_enabled" => array(
        "label" => __("Cache", "we"),
        "description" => __("This enable the wordpress assets cache.", "we"),
        "default_value" => "true"
      ),
      "assets_version" => array(
        "label" => __("Version", "we"),
        "description" => __("Using this function automatically generate the version number. You can of course decide to use a hard-coded version number/string if preferred by changing this preference.", "we"),
        "default_value" => "false"
      ),
      "css_compass_path" => array(
        "label" => __("Compass Path", "we"),
        "description" => __("The compass path on your dev environment (you can found it with: 'which wordless_compass').", "we"),
        "default_value" => "/opt/wordless/compass"
      ),
      "css_output_style" => array(
        "label" => __("Css Compass output style", "we"),
        "description" => __("The output style for the compiled css. One of: nested, expanded, compact, or compressed.", "we"),
        "default_value" => "compressed"
      ),
      "css_require_libs" => array(
        "label" => __("Css require libs", "we"),
        "description" => __("Additionale Gem Libraries comma separeted.", "we"),
        "default_value" => ""
      ),
      "css_lessc_path" => array(
        "label" => __("Less path", "we"),
        "description" => __("The Less path on your dev environment.", "we"),
        "default_value" => ""
      ),
      "css_compress" => array(
        "label" => __("Less compression", "we"),
        "description" => __("Allow or disallow Less output compression.", "we"),
        "default_value" => "false"
      ),
      "js_ruby_path" => array(
        "label" => __("Ruby path", "we"),
        "description" => __("The ruby path on your dev environment (you can found it with: 'which wordless_ruby').", "we"),
        "default_value" => "/opt/wordless/ruby"
      ),
      "js_yui_compress" => array(
        "label" => __("JS compression", "we"),
        "description" => __("Allow or disallow JS output compression.", "we"),
        "default_value" => "false"
      ),
      "js_yui_munge" => array(
        "label" => __("JS vars compression", "we"),
        "description" => __("Allow or disallow JS vars compression.", "we"),
        "default_value" => "false"
      )
    );

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      foreach ($wordless_preferences as $name => $properties){
        $value = trim($_POST[$name]);
        if ($name == "assets_preprocessors" || $name == 'css_require_libs') {
          $value = array_map('trim', explode(',', $value));
        }
        update_option($name, $value);
        $values[$name] = $value;
      }
      // create new file scheme
      $wl_pref_new = "<?php\n/*\n* This is an auto-generated preferences file. If you want to configure Wordless preferences go to WordPress backend.\n*/\n\n";
      $wl_pref_new .= "Wordless::set_preference(\"assets.preprocessors\", ". var_export($values['assets_preprocessors'], true) .");\n";
      if ($values['assets_cache_enabled'] == "true") $wl_pref_new .= "Wordless::set_preference(\"assets.cache_enabled\", true);\n";
      elseif ($values['assets_cache_enabled'] == "false") $wl_pref_new .= "Wordless::set_preference(\"assets.cache_enabled\", false);\n";
      if ($values['assets_version'] == "true") $wl_pref_new .= "Wordless::set_preference(\"assets.version\", get_theme_version());\n";
      $wl_pref_new .= "Wordless::set_preference(\"css.compass_path\", \"". $values['css_compass_path'] ."\");\n";
      $wl_pref_new .= "Wordless::set_preference(\"css.output_style\", \"". $values['css_output_style'] ."\");\n";
      $wl_pref_new .= "Wordless::set_preference(\"css.require_libs\", ". var_export($values['css_require_libs'], true) .");\n";
      $wl_pref_new .= "Wordless::set_preference(\"css.lessc_path\", \"". $values['css_lessc_path'] ."\");\n";
      if ($values['css_compress'] == "true") $wl_pref_new .= "Wordless::set_preference(\"css.compress\", true);\n";
      elseif ($values['css_compress'] == "false") $wl_pref_new .= "Wordless::set_preference(\"css.compress\", false);\n";
      $wl_pref_new .= "Wordless::set_preference(\"js.ruby_path\", \"". $values['js_ruby_path'] ."\");\n";
      if ($values['js_yui_compress'] == "true") $wl_pref_new .= "Wordless::set_preference(\"js.yui_compress\", true);\n";
      elseif ($values['js_yui_compress'] == "false") $wl_pref_new .= "Wordless::set_preference(\"js.yui_compress\", false);\n";
      if ($values['js_yui_munge'] == "true") $wl_pref_new .= "Wordless::set_preference(\"js.yui_munge\", true);\n";
      elseif ($values['js_yui_munge'] == "false") $wl_pref_new .= "Wordless::set_preference(\"js.yui_munge\", false);\n";
      
      $wl_pref_file = get_template_directory() .'/config/initializers/wordless_preferences.php';
      
      if (file_put_contents($wl_pref_file, $wl_pref_new) === false) echo sprintf('<div class="error"><p>%s<p></div>', __("Something wrong!", "we"));

      echo sprintf('<div class="error"><p>%s<p></div>', __("Preferences saved!", "we"));
    }

    require 'admin/preferences_form.php';
  }
}

