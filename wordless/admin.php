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
	  echo '<div class="error"><p>';
    echo sprintf(
      __('Your current theme does seem to be a Wordless-compatible theme! <a href="%2$s" target="_blank">%1$s</a> (or <a href="%4$s" target="_blank">%3$s</a>)'),
      __('Create a new Wordless theme'),
      admin_url('themes.php?page=create_wordless_theme'),
      __('learn more about Wordless'),
      'https://github.com/welaika/wordless#readme'
    );
		echo "</p></div>";
	}

  public static function add_page() {
    $page = add_theme_page(
      'Create a new Wordless theme',
      'New Wordless theme',
      'edit_theme_options',
      'create_wordless_theme',
      array('WordlessAdmin', 'page_content')
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
}
