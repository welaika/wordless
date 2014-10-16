<?php

Wordless::require_once_dir(Wordless::join_paths(dirname(dirname(dirname(__FILE__))), "vendor/lorem"));
Wordless::require_once_dir(Wordless::join_paths(dirname(dirname(dirname(__FILE__))), "vendor/lorem/PlaceholderImage"));

/**
 * Provides methods for use of placeholders (images or text).
 * 
 * @copyright welaika (c) 2011-2014 - MIT License
 * 
 * @ingroup helperclass
 */
class FakerHelper {

  /**
   * Generate a placeholder image.
   * 
   * Using the specified sevice provide a placeholder image.
   * 
   * Placeholder Services available are described here @ref placeholders.
   *
   * To implement your service please check the @ref placeholderservices.
   * 
   * @param int $width
   *   The width of the placeholder image.
   * @param int $height
   *   The height of the placeholder image.
   * @param array $options
   *   (optional) An option array to be passed to the PHP Class handling the 
   *   placeholder service. See the placeholder service docs for details.
   * 
   * @return @e string
   *   The @e URL of the placeholder image generated with the specified service.
   * 
   * @ingroup helperfunc
   */
  public function placeholder_image($width, $height, $options = array()) {
    $services_class = array(
      'placehold'   => 'PlaceholdImage',
      'lorem_pixel' => 'LoremPixelImage',
      'placecage'   => 'PlaceCageImage',
      'fillmurray'  => 'FillMurrayImage',
      'placepuppy'  => 'PlacePuppyImage',
      'phold'       => 'PHoldImage'
    );

    $service = isset($options['service']) ? $options['service'] : NULL;
    $service = isset($service) ? $service : 'placehold';

    $service_class = $services_class[$service];
    if (class_exists($service_class)) {
      $service = new $service_class($width, $height, $options);
      return $service->url();
    } else {
      render_error("No placeholder image service called #{$service} exists!");
    }
  }

  /**
   * Generate placeholder text.
   * 
   * Using the famous Lorem Ipsum base text, generate a dummy text based on 
   * specified options.
   * 
   * @param int $count
   *   The length in words of the dummy text.
   * @param array $options
   *   (optional) An array of options to manage text generation. Options 
   *   available are:
   *   - html: wheter to build HTML content or plain content. Default to false.
   *   - lorem: if the text must start with "Lorem ipsum..." or not.
   *     Default to true.
   * 
   * @return @e string 
   *   The generated dummy text.
   * 
   * @ingroup helperfunc
   */
  public function placeholder_text($count, $options = array()) {
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
