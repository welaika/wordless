<?php
function javascript_include_tag()
 { $helper = Wordless::helper('AssetTagHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'javascript_include_tag'), $args); }

