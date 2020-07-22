<?php
function link_to()
 { $helper = Wordless::helper('TagHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'link_to'), $args); }

