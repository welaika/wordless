<?php

require_once dirname(dirname(__FILE__)).'/PlaceholderImage.php';

/**
 * Implements the LoremPixel service.
 * 
 * @ingroup helperclass
 * 
 * @see http://lorempixel.com
 */
class LoremPixelImage extends PlaceholderImage {

  private static $offset = 0;

  /**
   * Implements PlaceholderImage::url().
   * 
   * @par Available options are:
   * - category
   * - gray
   * - offset
   */
  public function url() {
    $options = array_merge(
      array('category' => 'nightlife'),
      $this->options
    );

    $url =  "http://lorempixel.com";

    if (isset($options["gray"]) && $options["gray"]) {
      $url .= "/g";
    }

    $url .= "/{$this->width}/{$this->height}";
    $url .= "/{$options["category"]}";

    $offset = self::$offset;
    $url .= "/{$offset}";

    if (isset($options["text"])) {
      $encoded_text = rawurlencode($options["text"]);
      $url .= "/{$encoded_text}";
    }

    self::$offset++;

    return $url;
  }
}
