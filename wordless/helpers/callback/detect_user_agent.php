<?php
function detect_user_agent()
 { $helper = Wordless::helper('MediaHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'detect_user_agent'), $args); }

