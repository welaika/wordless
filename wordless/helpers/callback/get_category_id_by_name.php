<?php
function get_category_id_by_name()
 { $helper = Wordless::helper('QueryHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_category_id_by_name'), $args); }

