<?php
function stylesheet_url()
 { $helper = Wordless::helper('UrlHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'stylesheet_url'), $args); }

