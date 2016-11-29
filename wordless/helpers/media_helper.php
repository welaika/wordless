<?php

/**
 * Implements media interaction; mostly images manipulation and
 *
 * @ingroup helperclass
 */

class MediaHelper {

  /**
   * Includes a PHP class to detect mobile devices and user agent details.
   *
   * \code{.php}
   *  $detect = detect_user_agent()
   *  $detect->isMobile()
   * \endcode
   *
   *  @return object
   *
   * @see https://github.com/serbanghita/Mobile-Detect
   * \note
   *    Using Mobile Detect - Version 2.5.2 - commit b5b8992dbe
   *
   * @ingroup helperfunc
   */
  function detect_user_agent(){
    $detect = new Mobile_Detect();
    return $detect;
  }

  /**
   * Get the attached files in specified post.
   *
   * @param int $post_id
   *   The ID of the post of which we need to retrieve attachments.
   *
   * @return array
   *   List of post attachment objects.
   *
   * @ingroup helperfunc
   */
  function get_post_attachments($post_id) {
    $args = array(
      'post_type' => 'attachment',
      'numberposts' => -1,
      'post_status' => null,
      'post_parent' => $post_id
    );

    return get_posts($args);
  }

  /**
   * Get the attached files in current post.
   *
   * @return array
   *   List of post attachment objects.
   *
   * @ingroup helperfunc
   */
  function get_current_post_attachments() {
    return get_post_attachments(get_queried_object_id());
  }

  /**
   * Resizes the specified image to the specified dimensions.
   *
   * The crop will be centered relative to the image.
   *
   * @param string $src
   *   The path to the image to be resized
   * @param int $width
   *   The width at which the image will be cropped
   * @param int $height
   *   The height at which the image will be cropped
   *
   * @return string
   *   The valid URL to the image
   *
   * Cropped images are stored in template tmp folder.
   *
   * @ingroup helperfunc
   */
  function resize_image($src, $width, $height){
    // initializing
    $save_path = Wordless::theme_temp_path();
    $img_filename = Wordless::join_paths($save_path, md5($width . 'x' . $height . '_' . basename($src)) . '.jpg');

    // if file doesn't exists, create it
    if (!file_exists($img_filename)) {
      $to_scale = 0;
      $to_crop = 0;

      // Get orig dimensions
      list ($width_orig, $height_orig, $type_orig) = getimagesize($src);

      // get original image ... to improve!
      switch($type_orig){
        case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($src);
          break;
        case IMAGETYPE_PNG: $image = imagecreatefrompng($src);
          break;
        case IMAGETYPE_GIF: $image = imagecreatefromgif($src);
          break;
        default:
          return;
      }

      // which is the new smallest?
      if ($width < $height)
        $min_dim = $width;
      else
        $min_dim = $height;

      // which is the orig smallest?
      if ($width_orig < $height_orig)
        $min_orig = $width_orig;
      else
        $min_orig = $height_orig;

      // image of the right size
      if ($height_orig == $height && $width_orig == $width) ; // nothing to do
      // if something smaller => scale
      else if ($width_orig < $width) {
        $to_scale = 1;
        $ratio = $width / $width_orig;
      }
      else if ($height_orig < $height) {
        $to_scale = 1;
        $ratio = $height / $height_orig;
      }
      // if both bigger => scale
      else if ($height_orig > $height && $width_orig > $width) {
        $to_scale = 1;
        $ratio_dest = $width / $height;
        $ratio_orig = $width_orig / $height_orig;
        if ($ratio_dest > $ratio_orig)
          $ratio = $width / $width_orig;
        else
          $ratio = $height / $height_orig;
      }
      // one equal one bigger
      else if ( ($width == $width_orig && $height_orig > $height) || ($height == $height_orig && $width_orig > $width) )
        $to_crop = 1;
      // some problem...
      else
        echo "ALARM";

      // we need to zoom to get the right size
      if ($to_scale == 1) {
        $new_width = $width_orig * $ratio;
        $new_height = $height_orig * $ratio;
        $image_scaled = imagecreatetruecolor($new_width, $new_height);
        // scaling!
        imagecopyresampled($image_scaled, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        $image = $image_scaled;

        if($new_width > $width || $new_height > $height)
          $to_crop = 1;
      }
      else {
        $new_width = $width_orig;
        $new_height = $height_orig;
      }

      // we need to crop the image
      if ($to_crop == 1) {
        $image_cropped = imagecreatetruecolor($width, $height);

        // find margins for images
        $margin_x = ($new_width - $width) / 2;
        $margin_y = ($new_height - $height) / 2;

        // cropping!
        imagecopy($image_cropped, $image, 0, 0, $margin_x, $margin_y, $width, $height);
        $image = $image_cropped;
      }

      // Save image
      imagejpeg($image, $img_filename, 95);
    }

    // Return image URL
    return Wordless::join_paths(Wordless::theme_temp_path(), basename($img_filename));
  }


}

Wordless::register_helper("MediaHelper");
