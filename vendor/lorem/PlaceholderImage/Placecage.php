<?php

require_once dirname(dirname(__FILE__)).'/PlaceholderImage.php';

/**
 * Implements the PlaceCage service.
 * 
 * @ingroup helperclass
 * 
 * @see  http://placecage.com
 */
class PlaceCageImage extends PlaceholderImage {

  /**
   * Implements PlaceholderImage::url().
   * 
   * @par Available options are:
   * - calm
   * - gray
   * - crazy
   * - gif
   */
  public function url() {
    $options = $this->options;

    $url =  "http://placecage.com";

    if (isset($options["gray"]) && $options["gray"]) {
      $url .= "/g";
    } else if (isset($options["calm"]) && $options["calm"]) {
      $url .= "";
    } else if (isset($options["crazy"]) && $options["crazy"]) {
      $url .= "/c";
    } else if (isset($options["gif"]) && $options["gif"]) {
      $url .= "/gif";
    } 

    $url .= "/{$this->width}/{$this->height}";

    if (isset($options["text"])) {
      $encoded_text = rawurlencode($options["text"]);
      $url .= "/{$encoded_text}";
    }

    return $url;
  }
}