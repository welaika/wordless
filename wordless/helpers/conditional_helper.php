<?php
/**
 * Implements Wordpress Conditional Tags (http://codex.wordpress.org/Conditional_Tags)
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

  /**
   * Check if is a page when WPML is in use; takes in consideration
   *   all the translations.
   *
   * @param  string|array   $page_title The page title as string, or multiple
   *                                    page titles as an array.
   *
   * @return boolean        TRUE if is a page or a tranlated page or FALSE
   *
   * @ingroup helperfunc
   *
   * @warning Page title is not page slug nor page ID :)
   */
  function is_page_wpml( $page_title = array() ){
    if (!function_exists('icl_object_id'))
      return false;

    if (empty($page_title))
      return false;

    $pages = array();

    if (is_array($page_title)) {
      $pages = array_merge($pages, $page_title);
    } else {
      $pages[] = $page_title;
    }

    foreach ($pages as $page) {
      $pageObj = get_page_by_title( $page );
      $icl_object_id = array();
      $icl_object_id = icl_object_id( $pageObj->ID, 'page', true );

      if ( is_page( array( $icl_object_id, $page->ID ) ) )
        return true;
    }

    return false;

  }

}

Wordless::register_helper("ConditionalHelper");
