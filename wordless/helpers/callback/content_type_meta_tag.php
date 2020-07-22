<?php
function content_type_meta_tag()
 { $helper = Wordless::helper('TagHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'content_type_meta_tag'), $args); }

