<?php
function new_post_type()
 { $helper = Wordless::helper('ModelHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'new_post_type'), $args); }

