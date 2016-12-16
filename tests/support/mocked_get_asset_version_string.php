<?php

class AssetTagHelperTestVersion extends AssetTagHelper {
  var $mock;

  function __construct($mock) {
    $this->mock = $mock;
    parent::__construct();
  }

  protected function get_asset_version_string() {
    return $this->mock;
  }

  function asset_version($source) {
    return $source . "?ver=" . $this->mock;
  }
}
