<?php
/**
* Provides methods for generating HTML that links views to assets
* such as images, javascripts, stylesheets, and feeds. These methods do not
* verify the assets exist before linking to them.
*
* @ingroup helperclass
*/
class AssetTagHelper {

    function __construct() {}

    /**
    * Return the theme version, based on prederence set in Wordless config file.
    *
    * @return string
    *   The assets version string
    *
    * @ingroup helperfunc
    */
    protected function get_asset_version_string() {
        return Wordless::preference('assets.version', NULL);
    }

    /**
    * Appends version information to asset source.
    *
    * @param string $source
    *   The path to the asset source.
    * @return @e string
    *   The path to the asset source with version information appended to the query string.
    *
    * @ingroup helperfunc
    */
    function asset_version($source) {
        $version = $this->get_asset_version_string();

        if (isset($version))
            $source .= sprintf("?ver=%s", $version);
        return $source;
    }

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
    public function auto_discovery_link_tag($model_or_url = "posts", $type = "rss", $additional_options = NULL) {

        $options = array(
            "rel" => "alternate",
            "title" => strtoupper($type)
            );

        switch ($type) {
            case "atom":
            $options['type'] = "application/atom+xml";
            break;
            case "rss":
            default:
            $type = "rss";
            $options['type'] = "application/rss+xml";
            break;
        }

        switch ($model_or_url) {
            case "posts":
            case "comments":
            $options['href'] = get_feed_url($model_or_url, $type);
            break;
            default:
            $options['href'] = $model_or_url;
        }

        if (is_array($additional_options)) {
            $options = array_merge($options, $additional_options);
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
    function favicon_link_tag($source = "favicon.ico", $attributes = NULL) {
        if (!preg_match("/^(https?|\/)/", $source)) {
            $source = image_url($source);
        }

        $info = pathinfo($source);

        $mime_types = array(
            "ico" => "image/vnd.microsoft.icon",
            "png" => "image/png",
            "jpeg" => "image/jpeg",
            "jpg" => "image/jpg"
            );

        $options = array(
            "rel"  => "icon",
            "href" => $source,
            "type" => $mime_types[strtolower($info['extension'])]
            );

        if(is_array($attributes)){
            $options = array_merge($options, $attributes);
        }

        $output = content_tag("link", NULL, $options);
        $output .= content_tag("link", NULL, array_merge($options, array("rel" => "shortcut icon")));

        return $output;
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
                return get_bloginfo('rdf_url');
                case "rss1":
                case "rss092":
                return get_bloginfo('rss_url');
                case "atom":
                return get_bloginfo('atom_url');
                case "rss":
                case "rss2":
                default:
                return get_bloginfo('rss2_url');
            }
        } elseif ($model=="comments") {
            return get_bloginfo('comments_rss2_url');
        }

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
        if (!(is_absolute_url($source) || is_root_relative_url($source))) {
            $source = image_url($source);
        }

        $info = pathinfo($source);

        $extension = isset($info['extension']) ? $info['extension'] : '';

        $options = array(
            "src"  => $this->asset_version($source),
            "alt"  => capitalize(basename($source,'.' . $extension))
            );

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
            $options = array("src" => $sources);
            if (is_array($attributes)){
                $options = array_merge($options, $attributes);
            }
            return content_tag("video", NULL, $options);
        }
    }

    /**
    * Returns a stylesheet link tag for the sources specified as arguments. If
    * you don’t specify an extension, ".css" will be appended automatically.
    * Relative paths are assumed to be relative to assets/javascripts.
    * If the last argument is an array, it will be used as tag attributes.
    *
    * @return @e string
    *   A valid \<link /\> HTML tag.
    *
    * @ingroup helperfunc
    *
    * @see TagHelper::content_tag()
    *
    */
    function stylesheet_link_tag() {
        $sources = func_get_args();
        $tags = array();

        $attributes = NULL;
        if (is_array($sources[count($sources) - 1])) {
            $attributes = array_pop($sources);
        }

        foreach ($sources as $source) {
            if (!is_absolute_url($source)) {
                $source = stylesheet_url($source);
                if (!preg_match("/\.css$/", $source)) $source .= ".css";
                $source = $this->asset_version($source);
            }
            $options = array(
                "href"  => $source,
                "media" => "all",
                "rel"   => "stylesheet",
                "type"  => "text/css"
                );
            if(is_array($attributes)){
                $options = array_merge($options, $attributes);
            }
            $tags[] = content_tag("link", NULL, $options);
        }

        return join("\n", $tags);
    }

    /**
    * Returns an HTML script tag for each of the sources provided as arguments.
    * Sources may be paths to JavaScript files. Relative paths are assumed to be
    * relative to assets/javascripts. When passing paths, the ".js" extension is
    * optional.
    * If the last argument is an array, it will be used as tag attributes.
    *
    * @return @e string
    *   A valid \<script /\> HTML tag.
    *
    * @ingroup helperfunc
    *
    * @see TagHelper::content_tag()
    *
    */
    function javascript_include_tag() {
        $sources = func_get_args();

        $attributes = NULL;
        if (is_array($sources[count($sources) - 1])) {
            $attributes = array_pop($sources);
        }

        $tags = array();

        foreach ($sources as $source) {
            // only http[s] or // (leading double slash to inherit the protocol)
            // are treated as absolute url
            if (!is_absolute_url($source)) {
                $source = javascript_url($source);
                if (!preg_match("/\.js$/", $source)) $source .= ".js";
                $source = $this->asset_version($source);
            }
            $options = array(
                "src"  => $source,
                "type"  => "text/javascript"
                );
            if(is_array($attributes)){
                $options = array_merge($options, $attributes);
            }
            $tags[] = content_tag("script", "", $options);
        }

        return join("\n", $tags);
    }

}

Wordless::register_helper("AssetTagHelper");
