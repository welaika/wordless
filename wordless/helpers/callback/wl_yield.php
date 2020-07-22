<?php
function wl_yield()
 { $helper = Wordless::helper('RenderHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'wl_yield'), $args); }

