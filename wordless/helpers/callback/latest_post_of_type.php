<?php
function latest_post_of_type()
 { $helper = Wordless::helper('QueryHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'latest_post_of_type'), $args); }

