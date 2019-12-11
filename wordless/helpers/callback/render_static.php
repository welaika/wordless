<?php
function render_static()
 { $helper = Wordless::helper('RenderHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'render_static'), $args); }

