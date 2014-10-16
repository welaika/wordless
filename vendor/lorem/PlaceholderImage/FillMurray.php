<?php

require_once dirname(dirname(__FILE__)).'/PlaceholderImage.php';

/**
 * Implements the Fill Murray service.
 * 
 * @ingroup helperclass
 * 
 * @see  http://fillmurray.com
 */
class FillMurrayImage extends PlaceholderImage {

  /**
   * Implements PlaceholderImage::url().
   * 
   * @par Available options are:
   * - gray
   */
  public function url() {
    $options = $this->options;

    $url =  "http://fillmurray.com";

    if (isset($options["gray"]) && $options["gray"]) {
      $url .= "/g";
    }

    $url .= "/{$this->width}/{$this->height}";

    if (isset($options["text"])) {
      $encoded_text = rawurlencode($options["text"]);
      $url .= "/{$encoded_text}";
    }

    return $url;
  }
}