<?php

class PlaceholderImage {

  function __construct($width, $height, $options = array()) {
    $this->width = $width;
    $this->height = $height;
    $this->options = $options;
  }

  function url() {
    return "";
  }

}
