<?php
function get_the_first_categories_except()
 { $helper = Wordless::helper('QueryHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_the_first_categories_except'), $args); }

