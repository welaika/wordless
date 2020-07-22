<?php
function is_absolute_url()
 { $helper = Wordless::helper('UrlHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'is_absolute_url'), $args); }

