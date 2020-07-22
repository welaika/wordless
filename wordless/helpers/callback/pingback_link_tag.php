<?php
function pingback_link_tag()
 { $helper = Wordless::helper('TagHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'pingback_link_tag'), $args); }

