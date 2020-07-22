<?php
function get_partial_content()
 { $helper = Wordless::helper('RenderHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_partial_content'), $args); }

