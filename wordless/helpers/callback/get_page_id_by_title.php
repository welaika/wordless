<?php
function get_page_id_by_title()
 { $helper = Wordless::helper('QueryHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_page_id_by_title'), $args); }

