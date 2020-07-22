<?php
function is_valid_url()
 { $helper = Wordless::helper('TextHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'is_valid_url'), $args); }

