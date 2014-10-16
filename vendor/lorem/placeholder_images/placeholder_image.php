<?php
/**
 * Abstract class, not meant to be used directly but to be extended to bind some
 * online image placeholder service to Wordless.
 *
 * @ingroup placeholders
 */
class PlaceholderImage {

  /**
   * Create a new class instance with the specified options.
   * 
   * @attention This method must not be overriden in subclasses.
   * 
   * @param int $width
   *   The width of the placeholder image.
   * @param int $height
   *   The height of the placeholder image.
   * @param array $options
   *   An option array to be passed to the placeholder service.
   */
  public function __construct($width, $height, $options = array()) {
    $this->width = $width;
    $this->height = $height;
    $this->options = $options;
  }

  /**
   * Builds the url to generate the dummy image.
   * 
   * This function heavily depends on the service used to generate the image.
   *
   * @attention This method @b must be overriden in subclasses. All the options
   *   needed in url() must be passed to the subclass via the $options array
   *   passed as arguments of the class contructor.
   *   See docs related to the service you'd like to use for more information
   *   about availabe options.
   *
   * @return @e string
   *   The string related to the online service to generate the dummy content.
   */ 
  public function url() {
    return "";
  }
}
