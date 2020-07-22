<?php
function stylesheet_link_tag()
 { $helper = Wordless::helper('AssetTagHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'stylesheet_link_tag'), $args); }

