<?php

require_once 'placeholder_image.php';

class LoremPixelImage extends PlaceholderImage {

  private static $offset = 0;

  function url() {
    $options = array_merge(
      array('category' => 'nightlife'),
      $this->options
    );

    $url =  "http://lorempixel.com";

    if ($options["gray"]) {
      $url .= "/g";
    }

    $url .= "/{$this->width}/{$this->height}";
    $url .= "/{$options["category"]}";

    $offset = self::$offset;
    $url .= "/{$offset}";

    if ($options["text"]) {
      $encoded_text = rawurlencode($options["text"]);
      $url .= "/{$encoded_text}";
    }

    self::$offset++;

    return $url;
  }
}
