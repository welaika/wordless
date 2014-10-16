<?php

require_once dirname(dirname(__FILE__)).'/PlaceholderImage.php';

/**
 * Implements the PlacePuppy service.
 * 
 * @ingroup helperclass
 * 
 * @see  http://placepuppy.it/
 */
class PlacePuppyImage extends PlaceholderImage {

  /**
   * Implements PlaceholderImage::url().
   */
  public function url() {
    $options = $this->options;

    $url =  "http://placepuppy.it/";

    $url .= "/{$this->width}/{$this->height}";

    if (isset($options["text"])) {
      $encoded_text = rawurlencode($options["text"]);
      $url .= "/{$encoded_text}";
    }

    return $url;
  }
}