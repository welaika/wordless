<?php

require_once 'placeholder_image.php';

class PlaceholdImage extends PlaceholderImage {

  function url() {
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

    if ($options["text"]) {
      $encoded_text = rawurlencode($options["text"]);
      $url .= "&text={$encoded_text}";
    }

    return $url;
  }
}
