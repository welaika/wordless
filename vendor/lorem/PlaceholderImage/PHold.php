<?php

require_once dirname(dirname(__FILE__)).'/PlaceholderImage.php';

/**
 * Implements the P-Hold service.
 * 
 * @ingroup helperclass
 * 
 * @see http://p-hold.com/
 */
class PHoldImage extends PlaceholderImage {

  /**
   * Implements PlaceholderImage::url().
   * 
   * @par Available options are:
   * - keyword
   * - gray
   * - offset
   */
  public function url() {
    $options = $this->options;

    $url =  "http://p-hold.com";

    if (isset($options["keyword"])) {
      $url .= "/{$options["keyword"]}";
    }

    $url .= "/{$this->width}/{$this->height}";

    if (isset($options["gray"]) && $options["gray"]) {
      $url .= "/gray";
    } else if (isset($options["blur"]) && $options["blur"]) {
      $url .= "/blur";
    } else if (isset($options["hexcolor"])) {
      $url .= "/{$options["hexcolor"]}";
    }

    if (isset($options["text"])) {
      $encoded_text = rawurlencode($options["text"]);
      $url .= "/{$encoded_text}";
    }

    return $url;
  }
}