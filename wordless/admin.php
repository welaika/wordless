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
      add_action('admin_menu', array('WordlessAdmin', 'add_page'));
    }
  }

  public static function add_notice() {
    // Get a list of missing directories
    $dirs_missing = Wordless::theme_is_wordless_compatible(true);

    echo '<div class="error"><p>';
    echo sprintf(
      __('Your current theme does seem to be a Wordless-compatible theme! <a href="%2$s" target="_blank">%1$s</a> (or <a href="%4$s" target="_blank">%3$s</a>)'),
      __('Create a new Wordless theme'),
      admin_url('themes.php?page=create_wordless_theme'),
      __('learn more about Wordless'),
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
    $page = add_theme_page(
      'Create a new Wordless theme',
      'New Wordless theme',
      'edit_theme_options',
      'create_wordless_theme',
      array('WordlessAdmin', 'page_content')
    );
    $page = add_theme_page(
      'Setting Wordless preferences',
      'Wordless preferences',
      'edit_theme_options',
      'wordless_preferences',
      array('WordlessAdmin', 'preferences_content')
    );
  }

  public static function page_content() {
    $theme_options = array(
      "theme_name" => array(
        "label" => "Theme Name",
        "description" => "This will be the name displayed inside WordPress.",
        "default_value" => "Wordless"
      ),
      "theme_path" => array(
        "label" => "Theme Directory",
        "description" => "Specify the <code>wp-content/themes</code> subdirectory name for this theme.",
        "default_value" => "wordless"
      ),
      "chmod_set" => array(
        "label" => "Permissions",
        "description" => "Specify three octal number components specifying access restrictions",
        "default_value" => "0664"
      )
    );
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      // Form validation
      $valid = true;
      foreach ($theme_options as $name => $properties) {
        $value = $_POST[$name];
        if (empty($value)) {
          $theme_options[$name]['error'] = "This field is required!";
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
        "label" => "Preprocessors",
        "description" => "List Preprocessors you need comma separeted (ex: if you use Less replace CompassPreprocessor with LessPreprocessor).",
        "default_value" => "SprocketsPreprocessor, CompassPreprocessor"
      ),
      "assets_cache_enabled" => array(
        "label" => "Cache",
        "description" => "This enable the wordpress assets cache.",
        "default_value" => "true"
      ),
      "assets_version" => array(
        "label" => "Version",
        "description" => "Using this function automatically generate the version number. You can of course decide to use a hard-coded version number/string if preferred by changing this preference.",
        "default_value" => "false"
      ),
      "css_compass_path" => array(
        "label" => "Compass Path",
        "description" => "The compass path on your dev environment (you can found it with: 'which wordless_compass').",
        "default_value" => "/opt/wordless/compass"
      ),
      "css_output_style" => array(
        "label" => "Css Compass output style",
        "description" => "The output style for the compiled css. One of: nested, expanded, compact, or compressed.",
        "default_value" => "compressed"
      ),
      "css_require_libs" => array(
        "label" => "Css require libs",
        "description" => "Additionale Gem Libraries comma separeted.",
        "default_value" => ""
      ),
      "css_lessc_path" => array(
        "label" => "Less path",
        "description" => "The Less path on your dev environment.",
        "default_value" => ""
      ),
      "css_compress" => array(
        "label" => "Less compression",
        "description" => "Allow or disallow Less output compression.",
        "default_value" => "false"
      ),
      "js_ruby_path" => array(
        "label" => "Ruby path",
        "description" => "The ruby path on your dev environment (you can found it with: 'which wordless_ruby').",
        "default_value" => "/opt/wordless/ruby"
      ),
      "js_yui_compress" => array(
        "label" => "JS compression",
        "description" => "Allow or disallow JS output compression.",
        "default_value" => "false"
      ),
      "js_yui_munge" => array(
        "label" => "JS vars compression",
        "description" => "Allow or disallow JS vars compression.",
        "default_value" => "false"
      )
    );

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      foreach ($wordless_preferences as $name => $properties){
        $value = str_replace(" ", "", $_POST[$name]);
        if (($name == "assets_preprocessors" || $name == 'css_require_libs') && (strlen($value) > 0)) {
          $value = explode(',', $value);
        }
        update_option($name, $value);
      }
      echo '<div class="error"><p>Preferences saved!<p></div>';
    }
  
    require 'admin/preferences_form.php';
  }
}
