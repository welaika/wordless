<?php
function get_post_attachments()
 { $helper = Wordless::helper('MediaHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_post_attachments'), $args); }

