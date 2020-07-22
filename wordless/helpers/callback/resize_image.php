<?php
function resize_image()
 { $helper = Wordless::helper('MediaHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'resize_image'), $args); }

