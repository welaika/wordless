<?php
function get_the_filtered_content()
 { $helper = Wordless::helper('QueryHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_the_filtered_content'), $args); }

