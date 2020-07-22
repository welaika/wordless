<?php
function image_tag()
 { $helper = Wordless::helper('AssetTagHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'image_tag'), $args); }

