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
 * Create Cache management menu & render cache management page
 *
 * A default page is rendered, but you can make your own function and replace it instead of Wordless::render_static_cache_menu
 * Enable cache management by uncommenting line below.
 */
// add_action('admin_menu', 'cache_management');

function cache_management() {
  add_menu_page(__('Gestione cache'), __('Gestione cache'), 'edit_posts', 'cache-management', 'Wordless::render_static_cache_menu', 'dashicons-html', 75 );
}
