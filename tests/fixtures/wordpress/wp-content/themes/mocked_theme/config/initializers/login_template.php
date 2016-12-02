<?php 

/*
 * Enable custom login style uncommenting the line below
 */
// add_action("login_head", "my_login_head");

/* 
 * Change login page style adding your custom css in $output
 */
function my_login_head() {
  $output = "<style>";
  $output .= "body{}body.login #login h1 a {}";
  $output .= "</style>";
  echo $output;
}

 
/*
 * Change title for login screen uncommenting the line below
 */
// add_filter('login_headertitle', create_function(false,"return get_bloginfo('site');"));
 
/*
 * Change url for login screen uncommenting the line below
 */
// add_filter('login_headerurl', create_function(false,"return home_url();"));