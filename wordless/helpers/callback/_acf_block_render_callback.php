<?php
function _acf_block_render_callback()
 { $helper = Wordless::helper('AcfGutenbergBlockHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, '_acf_block_render_callback'), $args); }

