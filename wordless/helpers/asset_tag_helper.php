<?php
/**
 * This module provides methods for generating HTML that links views to assets 
 * such as images, javascripts, stylesheets, and feeds. These methods do not 
 * verify the assets exist before linking to them.
 * 
 * @ingroup helperclass
 */
class AssetTagHelper {

  /**
   * Builds a valid \<audio /\> HTML tag.
   * 
   * @param string $source
   *   The path to the audio source.
   * @param array $attributes
   *   (optional) An array of HTML attributes to be added to the rendered tag.
   * @return @e string
   *   A valid \<audio /\> HTML tag.
   * 
   * @ingroup helperfunc
   */
  function audio_tag($source, $attributes = array()){
    $options = array_merge(array("src" => $source), $attributes);

    return content_tag("audio", NULL, $options);
  }

  /**
   * Builds a valid \<link /\> HTML tag to a feed ( rss or atom ).
   * 
   * @param string $type
   *   (optional) The type of the feed. Could be @e rss or @e atom. 
   *   Defaults to @e rss.
   * @param string|array $url_options
   *   (optional) ??
   * @param array $tag_options
   *   (optional) ??
   * @return @e string
   *   A valid \<link /\> HTML tag to a feed ( rss or atom ).
   * 
   * @see AssetTagHelper::get_feed()
   * @see TagHelper::content_tag()
   * 
   * @ingroup helperfunc
   * 
   * @todo complete docs
   * 
   * @doubt but really, what does this function name mean?? O.o and what it
   *   does??
   */
  public function auto_discovery_link_tag($type = "rss", $url_options = NULL, $tag_options = NULL) {

    $options = array();

    switch ($type) {
      case "rss":
        $options['type'] = "application/rss+xml";
        break;
      case "atom":
        $options['type'] = "application/atom+xml";
        break;
      default:
        $type = "rss";
        $options['type'] = "application/rss+xml";
        break;
    }

    switch ($url_options) {
      case is_string($url_options):
        $options['href'] = $url_options;
      case is_array($url_options):
        if (isset($url_options['feed'])) {
          $options['href'] = $this->get_feed($type, $url_options['feed']);
        } elseif (isset($url_options['href'])) {
          $options['href'] = $url_options['href'];
        } else {
          $options['href'] = "#";
        }
      case is_null($url_options):
      default:
        $options['href'] = $this->get_feed($type, "posts");
    }

    if (is_array($tag_options)) {
      $options = array_merge($options, $tag_options);
    }

    return content_tag("link", NULL, $options);
  }

  /**
   * Builds a valid \<link /\> HTML tag to a favicon.
   * 
   * @param string $source
   *   (optional) The path to the favicon file. Must be a valid path to an 
   *   .ico file.
   * @return @e string
   *   A valid \<link /\> HTML tag to a favicon.
   * 
   * @see TagHelper::content_tag()
   * 
   * @ingroup helperfunc
   */
  public function favicon_link_tag($source= "/favicon.ico", $attributes = array()) {
    $options = array( "rel"  => 'shortcut icon',
                      "href" => $source,
                      "type" => 'image/vnd.microsoft.icon');

    $options = array_merge($options, $attributes);

    return content_tag("link", NULL, $options);

  }

  /**
   * Returns a WP valid feed, depending on the type or feed requested and of the
   * content for which the feed is created.
   * 
   * Post's feeds are different from comment's feeds, this function return the
   * correct feed ( rss/atom ) for the specified content.
   * 
   * @param string $type
   *   The type of the feed. Can be @b rdf, @b rss1, @b rss092, @b rss, @b rss2.
   * @param string $model
   *   Return the correct feed type, depending on content type. Can be @b post 
   *   or @b comment.
   * 
   * @see https://codex.wordpress.org/Function_Reference/bloginfo
   */
  private function get_feed($type, $model) {
    if (($model=="posts") || ($model=="post"))  {
        switch ($type) {
          case "rdf":
            return bloginfo('rdf_url');
          case "rss1":
          case "rss092":
            return bloginfo('rss_url');
          case "atom":
            return bloginfo('atom_url');
          case "rss":
          case "rss2":
          default:
            return bloginfo('rss2_url');
        }
    }
    elseif (($model=="comments") || ($model=="comment"))
      return bloginfo('comments_rss2_url');
    else
      return NULL;
  }

  /**
   * Builds a valid \<img /\> HTML tag.
   * 
   * @param string $source
   *   The source path to the image.
   * @param string|array $attributes
   *   (optional) A single HTML attribute or an array of HTML attributes to be
   *   added to the rendered tag.
   * @return @e string
   *   A valid \<img /\> HTML tag.
   * 
   * @ingroup helperfunc
   * 
   * @see SassExtentionsCompassFunctionsUrls::image_url()
   * @see TagHelper::content_tag()
   * 
   * @todo remove commented code
   * 
   * @doubt why there is a if ( $img ) where $img variable do not seems to be 
   *   used nor required nor appears somewhere?
   */
  public function image_tag($source, $attributes = NULL) {
    if (!preg_match("/^(http|\/)/", $img)) {
      $img = image_url($img);
    }

    $options = array( "src"  => $source );

    if(is_array($attributes)){

      /*if((isset($attributes['size']) && (preg_match('/^([0-9]+)(x([0-9]+))?(px)?$/',$attributes['size']))){
        list($options["width"], $options["height"]) = split("x", $attributes['size']);
        unset($attributes['size']);
      }*/

      $options = array_merge($options, $attributes);
    }

    return content_tag("img", NULL, $options);
  }

  /**
   * Builds a valid \<video /\> HTML tag.
   * 
   * @param string|array $sources
   *   A single source or an array of sources for the video tag.
   * @param array $attributes
   *  (optional) An array of HTML attributes to be added to the rendered tag.
   * @return @e string
   *   A valid \<video /\> HTML tag.
   * 
   * @ingroup helperfunc
   * 
   * @see TagHelper::content_tag()
   * 
   * @todo Is possible to use only one return, editing slightly the logic
   *
   * @doubt Why $attributes defaults to array() instead to NULL as in img_tag?
   * @doubt Why $attributes here cannot be a single string but must be an array?
   * @doubt If $source in foreach is not a string, what else could be?
   */
  public function video_tag($sources, $attributes = array()){
    if (is_array($sources)) {
      $html_content = "";

      foreach ($sources as $source) {
        if (is_string($source))
          $html_content .=  content_tag("source", NULL, array("src" => $source));
        else
          $html_content .=  content_tag("source", NULL, $source);
      }

      return content_tag("video", $html_content, $attributes);
    }
    else {
      $options = array_merge(array("src" => $sources), $attributes);
      return content_tag("video", NULL, $options);
    }
  }
}

Wordless::register_helper("AssetTagHelper");
