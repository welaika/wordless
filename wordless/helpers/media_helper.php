<?php 

/**
 * This module implement Media interaction
 * 
 * @ingroup helperclass
 */


class MediaHelper {
  
  /** This function include a library (Detector v0.8.5) that return information about browsers & devices (user agents).
   * To use it simply call the function UserAgent() in your view and the global var $ua or the other vars that you need.
   * More details here https://github.com/dmolsen/Detector.
   */

  function UserAgent(){
    global $ua;
    include(dirname(__FILE__) ."/../Detector/lib/Detector/Detector.php");
  }

  // Simple check if the device is mobile.

  function CheckMobileDevice($ua) {
    if ($ua->isTablet || $ua->isMobile || $ua->isMobileDevice){
      return true;
    } else {
      return false;
    }
  }

  // get the attached files in post ($post_id)

  function get_post_attachments($post_id) {
    $args = array(
      'post_type' => 'attachment',
      'numberposts' => -1,
      'post_status' => null,
      'post_parent' => $post_id
    );
    
    return get_posts($args);
  }

  // get the attached files in current post

  function get_current_attachment() {
    return get_post_attachment(get_queried_object_id());
  }

  
  


}

Wordless::register_helper("MediaHelper");