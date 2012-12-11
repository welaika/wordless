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
}

Wordless::register_helper("MediaHelper");