<?php
function get_theme_version()
 { $helper = Wordless::helper('ThemeHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_theme_version'), $args); }

