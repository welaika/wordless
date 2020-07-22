<?php
function is_post_type()
 { $helper = Wordless::helper('QueryHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'is_post_type'), $args); }

