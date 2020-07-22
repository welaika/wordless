<?php
function get_post_type_singular_name()
 { $helper = Wordless::helper('QueryHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_post_type_singular_name'), $args); }

