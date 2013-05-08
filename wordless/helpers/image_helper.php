<?php
/**
 * This module provides functions to interact with images.
 * 
 * @ingroup helperclass
 */
class ImageHelper {
  /**
   * Resizes the specified image to the specified dimensions.
   * 
   * The crop will be centered relative to the image.
   * If the files do not exists will be created and saved into the tmp/ folder.
   * After first creation, the same file will be served in response to calling
   * this function.
   * 
   * @param string $src
   *   The path to the image to be resized.
   * @param int $width
   *   The width at which the image will be cropped.
   * @param int $height
   *   The height at which the image will be cropped.
   * @return string
   *   The valid URL to the resized image.
   * 
   * @ingroup helperfunc
   */
  function resize_image($src, $width, $height){
    // initializing
    $save_path = get_theme_path() . '/tmp/';
    $img_filename = $save_path . md5($width . 'x' . $height . '_' . basename($src)) . '.jpg';

    // if file doesn't exists, create it ( else simply returns the image )
    if (!file_exists($img_filename)) {
      $to_scale = FALSE;
      $to_crop = FALSE;

      // Get orig dimensions
      list ($width_orig, $height_orig, $type_orig) = getimagesize($src);

      // get original image ... to improve! 
      switch ($type_orig) {
        case IMAGETYPE_JPEG:
          $image = imagecreatefromjpeg($src);
          break;
        case IMAGETYPE_PNG:
          $image = imagecreatefrompng($src);
          break;
        case IMAGETYPE_GIF:
          $image = imagecreatefromgif($src);
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
        $to_scale = TRUE;
        $ratio = $width / $width_orig;
      }
      else if ($height_orig < $height) {
        $to_scale = TRUE;
        $ratio = $height / $height_orig;
      }
      // if both bigger => scale
      else if ($height_orig > $height && $width_orig > $width) {
        $to_scale = TRUE;
        $ratio_dest = $width / $height;
        $ratio_orig = $width_orig / $height_orig;
        if ($ratio_dest > $ratio_orig)
          $ratio = $width / $width_orig;
        else 
          $ratio = $height / $height_orig;
      }
      // one equal one bigger
      else if (($width == $width_orig && $height_orig > $height) || ($height == $height_orig && $width_orig > $width))
        $to_crop = TRUE;
      // some problem...
      else
        trigger_error("Cannot resize image " . $src, E_USER_ERROR);

      // we need to zoom to get the right size
      if ($to_scale) {
        $new_width = $width_orig * $ratio;
        $new_height = $height_orig * $ratio;
        $image_scaled = imagecreatetruecolor($new_width, $new_height);
        // scaling!
        imagecopyresampled($image_scaled, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        $image = $image_scaled;

        if($new_width > $width || $new_height > $height)
          $to_crop = TRUE;
      }
      else {
        $new_width = $width_orig;
        $new_height = $height_orig;
      }

      // we need to crop the image  
      if ($to_crop) {
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
    return get_bloginfo("template_url") . '/tmp/' . basename($img_filename);
  }
}

Wordless::register_helper("ImageHelper");
