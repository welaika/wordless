<?php

require_once 'placeholder_image.php';

/**
 * Implements the Placehold service.
 * 
 * @ingroup helperclass
 * 
 * @see http://placehold.it
 */
class PlaceholdImage extends PlaceholderImage {

  /**
   * Implements PlaceholderImage::url().
   * 
   * @par Available options are:
   * - background_color
   * - foreground_color
   */
  public function url() {
    $options = array_merge(
      array(
        'background_color' => '777777',
        'foreground_color' => '444444'
      ),
      $this->options
    );

    $url =  "http://placehold.it/{$this->width}x{$this->height}";
    $url .= "/{$options["background_color"]}";
    $url .= "/{$options["foreground_color"]}";

    if (isset($options["text"])) {
      $encoded_text = rawurlencode($options["text"]);
      $url .= "&text={$encoded_text}";
    }

    return $url;
  }
}
