<?php
function active_if()
 { $helper = Wordless::helper('TextHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'active_if'), $args); }

