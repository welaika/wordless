<?php

/*
 * Remove Unwanted Admin Menu Items uncommenting the line below
 */
// add_action('admin_menu', 'remove_admin_menu_items');

/*
 * Populate $menu_items array to exclude Admin Menu Items. There's a list of common elements:
 * Appearance, Comments, Links, Media, Pages, Plugins, Posts, Settings, Tools, Users
 */
function remove_admin_menu_items() {
  $menu_items = array(__('Comments'),__('Links'),__('Posts'), __('Appearance'), __('Plugins'), __('Tools'), __('Settings'), __('Media'));
  global $menu;
  end ($menu);
  while (prev($menu)){
    $item = explode(' ',$menu[key($menu)][0]);
    if(in_array($item[0] != NULL?$item[0]:"" , $menu_items)){
    unset($menu[key($menu)]);}
  }
}

/*
 * Remove Update messages for all users uncommenting the line below
 */
// add_action('admin_menu','remove_update_message');

/*
 * This function is called by add_action('admin_menu') to remove update messages for all users
 */
function remove_update_message() {
  // sidebar messages
  remove_submenu_page('index.php', 'update-core.php');
  // topbar messages
  remove_action('admin_notices', 'update_nag', 3);
}

/*
 * Remove Dashboard widgets uncommenting the line below and settings remove_dashboard_widgets function
 */
// add_action('wp_dashboard_setup', 'remove_dashboard_widgets');

/*
 * Remove some widgets from dashboard page. Default setting remove incoming links and Right Now.
 */
function remove_dashboard_widgets() {
  // Globalize the metaboxes array, this holds all the widgets for wp-admin
  global $wp_meta_boxes;

  // Remove the incoming links widget
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);

  // Remove Right Now widget
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

/*
 * Remove some links in Admin bar uncommenting line below and setting $elements array in
 * remove_admin_bar_links function.
 */
// add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );


function remove_admin_bar_links() {
    global $wp_admin_bar;
    $elements = array('wp-logo', 'about', 'wporg', 'documentation', 'support-forums', 'feedback', 'updates', 'comments', 'new-content');
    foreach ($elements as $element) {
      $wp_admin_bar->remove_menu($element);
    }
}

/*
 * Disable theme switching uncommenting line below
 */
// add_action('admin_init', 'slt_lock_theme');

function slt_lock_theme() {
  global $submenu;
  unset($submenu['themes.php'][5]);
  unset($submenu['themes.php'][15]);
}

/*
 * Create Cache management menu
 */
add_action('admin_menu', 'cache_management');

function cache_management() {
  add_menu_page(__('Gestione cache'), __('Gestione cache'), 'edit_posts', 'cache-management', function(){ clear_cache_main_content(); }, 'dashicons-html', 75 );
}

function clear_cache_main_content() {
    $notice = '';

    if ( isset($_POST['clear-all-cache']) && 'all' === $_POST['clear-all-cache'] ) {
        Wordless::clear_theme_temp_path();
        $cached_files = Wordless::recursive_glob(Wordless::theme_temp_path());

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
      $path = Wordless::join_paths(Wordless::theme_temp_path(), $_POST['clear-single-cache']);
      unlink($path);
      $cached_files = Wordless::recursive_glob(Wordless::theme_temp_path());

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
        $cached_files = Wordless::recursive_glob(Wordless::theme_temp_path());
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
