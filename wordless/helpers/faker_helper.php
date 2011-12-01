<?php

require_once Wordless::join_paths(dirname(dirname(dirname(__FILE__))), 'vendor/lorem/LoremIpsum.class.php');
Wordless::require_once_dir(Wordless::join_paths(dirname(__FILE__), "placeholder_images"));

class FakerHelper {

  function placeholder_image($width, $height, $options = array()) {

    $services_class = array(
      'placehold' => 'PlaceholdImage',
      'lorem_pixel' => 'LoremPixelImage'
    );

    $service = $options['service'];
    $service = isset($service) ? $service : 'placehold';

    $service_class = $services_class[$service];
    if (class_exists($service_class)) {
      $service = new $service_class($width, $height, $options);
      return $service->url();
    } else {
      render_error("No placeholder image service called #{$service} exists!");
    }
  }

  function placeholder_text($count, $options = array()) {
    $options = array_merge(
      array(
        'html' => false,
        'lorem' => true
      ),
      $options
    );

    $generator = new LoremIpsumGenerator;

    $html_format = $options['html'] ? 'plain' : 'html';
    $start_with_lorem_ipsum = $options['lorem'];

    return ucfirst($generator->getContent($count, $html_format, $start_with_lorem_ipsum));
  }
}

Wordless::register_helper("FakerHelper");
