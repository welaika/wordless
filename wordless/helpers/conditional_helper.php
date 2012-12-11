<?php
/**
 * This module implement Wordpress Conditional Tags (http://codex.wordpress.org/Conditional_Tags)
 * 
 * @ingroup helperclass
 */

// check if the page is a subpage and return the parent post ID if true.
// if you pass an ID to function can check if is subpage of a specific parent page.

function is_subpage($id = "") {
    global $post;                              // load details about this page
    if ( is_page() && $post->post_parent ) {   // test to see if the page has a parent
        if ($id) {
          if ($id == $post->post_parent) return true;
          else return false;
        }
        else{
          return $post->post_parent;           // return the ID of the parent post
        }     
    } else {                                   // there is no parent so ...
        return false;                          // ... the answer to the question is false
    }
}