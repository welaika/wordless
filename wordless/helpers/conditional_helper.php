<?php
/**
 * This module implement Wordpress Conditional Tags (http://codex.wordpress.org/Conditional_Tags)
 * 
 * @ingroup helperclass
 */

class ConditionalHelper {

  /** 
   * Check if the page is a subpage and return the parent post ID if true.
   *
   * @param int $id
   *   (optional) If you pass an ID to the function, will check if the current
   *   page is subpage of the specified parent page.
   * 
   * @return int|bool
   *   Return the parent post if present or FALSE
   * 
   * @ingroup helperfunc
   */
  function is_subpage($id = NULL) {
    global $post;                              // load details about this page
    if (is_page() && $post->post_parent) {     // test to see if the page has a parent
      if ($id) {
        return ($id == $post->post_parent) ? $post->post_parent : FALSE;
      } else {
        return $post->post_parent;             // return the ID of the parent post
      }     
    } else {                                   // there is no parent so ...
      return FALSE;                            // ... the answer to the question is false
    }
  }
}

Wordless::register_helper("ConditionalHelper");
