<?php

require_once('simpletest/autorun.php');
require_once('support/mocked_bloginfo.php');
require_once('support/mocked_get_asset_version_string.php');
require_once('../wordless/wordless.php');
require_once('../wordless/helpers.php');

class AssetTagHelperTest extends UnitTestCase {
  var $mock;

  function setUp() {
    $this->mock = new AssetTagHelperTestVersion('1.0');
  }

  function test_asset_version() {
    $this->assertEqual(
      'http://example.com/path/to/source.css?ver=1.0',
      $this->mock->asset_version('http://example.com/path/to/source.css')
    );
  }

  function test_audio_tag() {
    $this->assertEqual(
      '<audio src="source"/>',
      audio_tag("source")
    );

    $this->assertEqual(
      '<audio src="source" id="test"/>',
      audio_tag("source", array("id" => "test"))
    );
  }

  function test_favicon_link_tag() {
    $this->assertEqual(
      '<link rel="icon" href="mocked_stylesheet_directory/assets/images/favicon.ico" type="image/vnd.microsoft.icon"/><link rel="shortcut icon" href="mocked_stylesheet_directory/assets/images/favicon.ico" type="image/vnd.microsoft.icon"/>',
      favicon_link_tag()
    );

    $this->assertEqual(
      '<link rel="icon" href="mocked_stylesheet_directory/assets/images/source.png" type="image/png"/><link rel="shortcut icon" href="mocked_stylesheet_directory/assets/images/source.png" type="image/png"/>',
      favicon_link_tag("source.png")
    );
  }

  function test_get_feed_url() {

    $this->assertEqual("mocked_rdf_url",  get_feed_url("posts", "rdf"));
    $this->assertEqual("mocked_rss_url", get_feed_url("posts", "rss1"));
    $this->assertEqual("mocked_rss_url", get_feed_url("posts", "rss092"));
    $this->assertEqual("mocked_atom_url", get_feed_url("posts", "atom"));
    $this->assertEqual("mocked_rss2_url", get_feed_url("posts", "rss"));
    $this->assertEqual("mocked_rss2_url", get_feed_url("posts", "rss2"));
    $this->assertEqual("mocked_rss2_url", get_feed_url("posts", "whatever"));

    $this->assertEqual("mocked_comments_rss2_url", get_feed_url("comments", "rss2"));
    $this->assertEqual("mocked_comments_rss2_url", get_feed_url("comments", "whatever"));
  }

  function test_auto_discovery_link_tag() {

    $this->assertEqual(
      '<link rel="alternate" title="RSS" type="application/rss+xml" href="mocked_rss2_url"/>',
      auto_discovery_link_tag()
    );

    $this->assertEqual(
      '<link rel="alternate" title="RSS" type="application/rss+xml" href="source"/>',
      auto_discovery_link_tag("source")
    );

    $this->assertEqual(
      '<link rel="alternate" title="ATOM" type="application/atom+xml" href="source"/>',
      auto_discovery_link_tag("source", "atom")
    );

  }

  function test_image_tag() {
    $this->assertEqual(
      '<img src="mocked_stylesheet_directory/assets/images/source.png?ver=1.0" alt="Source"/>',
      $this->mock->image_tag("source.png")
    );

    $this->assertEqual(
      '<img src="/source.png?ver=1.0" alt="Source"/>',
      $this->mock->image_tag("/source.png")
    );

    $this->assertEqual(
      '<img src="http://welaika.com/source.png?ver=1.0" alt="Source"/>',
      $this->mock->image_tag("http://welaika.com/source.png")
    );

    $this->assertEqual(
      '<img src="http://welaika.com/source.png?ver=1.0" alt="test" class="image"/>',
      $this->mock->image_tag("http://welaika.com/source.png", array("alt" => "test", "class"=>"image"))
    );

  }

  function test_video_tag() {

    $this->assertEqual(
      '<video src="source"/>',
      video_tag("source")
    );

    $this->assertEqual(
      '<video><source src="source"/><source src="alternate_source"/></video>',
      video_tag(array("source", "alternate_source"))
    );

    $this->assertEqual(
      '<video id="test"><source src="source"/><source src="alternate_source"/></video>',
      video_tag(array("source", "alternate_source"), array("id" => "test"))
    );

  }

  function test_javascript_include_tag() {
    $this->assertEqual(
      '<script src="mocked_stylesheet_directory/assets/javascripts/source.js?ver=1.0" type="text/javascript"></script>',
      $this->mock->javascript_include_tag("source")
    );

    $this->assertEqual(
      '<script src="mocked_stylesheet_directory/assets/javascripts/source.js?ver=1.0" type="text/javascript"></script>' . "\n" .
      '<script src="http://welaika.com/another_source.js" type="text/javascript"></script>',
      $this->mock->javascript_include_tag("source", "http://welaika.com/another_source.js")
    );

    $this->assertEqual(
      '<script src="mocked_stylesheet_directory/assets/javascripts/source.js?ver=1.0" type="text/javascript" charset="utf-8"></script>',
      $this->mock->javascript_include_tag("source", array("charset" => "utf-8"))
    );

     $this->assertEqual(
      '<script src="mocked_stylesheet_directory/assets/javascripts/source.js?ver=1.0" type="text/javascript" charset="utf-8"></script>' . "\n" .
      '<script src="https://welaika.com/another_source.js" type="text/javascript" charset="utf-8"></script>',
      $this->mock->javascript_include_tag("source", "https://welaika.com/another_source.js", array("charset" => "utf-8"))
    );

  }

  function test_stylesheet_link_tag() {
    $this->assertEqual(
      '<link href="mocked_stylesheet_directory/assets/stylesheets/source.css?ver=1.0" media="all" rel="stylesheet" type="text/css"/>',
      $this->mock->stylesheet_link_tag("source")
    );

    $this->assertEqual(
      '<link href="mocked_stylesheet_directory/assets/stylesheets/source.css?ver=1.0" media="all" rel="stylesheet" type="text/css"/>' . "\n" .
      '<link href="http://welaika.com/another_source.css" media="all" rel="stylesheet" type="text/css"/>',
      $this->mock->stylesheet_link_tag("source", "http://welaika.com/another_source.css")
    );

    $this->assertEqual(
      '<link href="mocked_stylesheet_directory/assets/stylesheets/source.css?ver=1.0" media="print" rel="stylesheet" type="text/css"/>',
      $this->mock->stylesheet_link_tag("source", array("media" => "print"))
    );

     $this->assertEqual(
      '<link href="mocked_stylesheet_directory/assets/stylesheets/source.css?ver=1.0" media="print" rel="stylesheet" type="text/css"/>' . "\n" .
      '<link href="https://welaika.com/another_source.css" media="print" rel="stylesheet" type="text/css"/>',
      $this->mock->stylesheet_link_tag("source", "https://welaika.com/another_source.css", array("media" => "print"))
    );
  }

}

