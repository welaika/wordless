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
   * Builds a valid \<link /\> HTML tag to a feed (rss or atom).
   * Returns a link tag that browsers and news readers can use to auto-detect an
   * RSS or ATOM feed. The type can either be @e rss (default) or @e atom. You
   * can modify the LINK tag itself in @e $tag_options.
   *
   * @param string $model_or_url
   *   (optional) The source of the feed. Can be a custom URL, @e posts or @e
   *   comments. In the latter case, the source will be returned by
   *   AssetTagHelper::get_feed_url()
   *   Defaults to @e posts.
   * @param string $type
   *   (optional) The type of the feed. Could be @e rss or @e atom.
   *   Defaults to @e rss.
   * @param array $tag_options
   *   (optional) An array of HTML attributes to be added to the rendered tag.
   * @return @e string
   *   A valid \<link /\> HTML tag to a feed (rss or atom).
   *
   * @see AssetTagHelper::get_feed_url()
   * @see TagHelper::content_tag()
   *
   * @ingroup helperfunc
   *
   */
  public function auto_discovery_link_tag($model_or_url = "posts", $type = "rss", $tag_options = NULL) {

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

    switch ($model_or_url) {
    case "posts":
    case "comments":
      $options['href'] = get_feed_url($model_or_url, $type);
    default:
      $options['href'] = $model_or_url;
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
   * @param array $attributes
   *   (optional) An array of HTML attributes to be added to the rendered tag.
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
   * correct feed (rss/atom) for the specified content.
   *
   * @param string $model
   *   Return the correct feed type, depending on content type. Can be @b posts
   *   or @b comments.
   * @param string $type
   *   The type of the feed. Can be @b rdf, @b rss1, @b rss092, @b rss, @b rss2.
   *
   * @ingroup helperfunc
   *
   * @see https://codex.wordpress.org/Function_Reference/bloginfo
   */
  public function get_feed_url($model, $type = "rss") {
    if ($model == "posts")  {
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
    elseif ($model=="comments")
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
   * @see TagHelper::content_tag()
   *
   */
  public function image_tag($source, $attributes = NULL) {
    if (!preg_match("/^(https?|\/)/", $source)) {
      $source = image_url($source);
    }

    $options = array( "src"  => $source );

    if(is_array($attributes)){
      $options = array_merge($options, $attributes);
    }

    return content_tag("img", NULL, $options);
  }

  /**
   * Builds a valid \<video /\> HTML tag.
   *
   * @param string|array $sources
   *   If sources is a string, a single video tag will be returned. If sources is an array, a video tag with nested source tags for each source will be returned.
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
   */
  public function video_tag($sources, $attributes = NULL){
    if (is_array($sources)) {
      $html_content = "";

      foreach ($sources as $source) {
        if (is_string($source))
          $html_content .= content_tag("source", NULL, array("src" => $source));
        else
          $html_content .= content_tag("source", NULL, $source);
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
