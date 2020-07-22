<?php
function arg()
 { $helper = Wordless::helper('UrlHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'arg'), $args); }

