<?php

class AssetTagHelperTestVersion extends AssetTagHelper {
  var $mock;

  function AssetTagHelperTestVersion($mock) {
    $this->mock = $mock;
    $this->AssetTagHelper();
  }

  protected function get_asset_version_string() {
    return $this->mock;
  }

  function asset_version($source) {
    return $source . "?ver=" . $this->mock;
  }
}
