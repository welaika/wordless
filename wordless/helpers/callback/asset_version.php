<?php
function asset_version()
 { $helper = Wordless::helper('AssetTagHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'asset_version'), $args); }

