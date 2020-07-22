<?php
function get_feed_url()
 { $helper = Wordless::helper('AssetTagHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_feed_url'), $args); }

