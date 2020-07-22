<?php
function render_partial()
 { $helper = Wordless::helper('RenderHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'render_partial'), $args); }

