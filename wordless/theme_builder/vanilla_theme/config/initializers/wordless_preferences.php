<?php

/*
 * Configure Wordless preferences in the WordPress backend.
 */

Wordless::set_preference("assets.preprocessors", get_option('assets_preprocessors'));
if (get_option('assets_cache_enabled') == 'true') Wordless::set_preference("assets.cache_enabled", true);
elseif (get_option('assets_cache_enabled') == 'false') Wordless::set_preference("assets.cache_enabled", false);
if (get_option('assets_version') == 'true') Wordless::set_preference("assets.version", get_theme_version());

Wordless::set_preference("css.compass_path", get_option('css_compass_path'));
Wordless::set_preference("css.output_style", get_option('css_output_style'));
Wordless::set_preference("css.require_libs", get_option('css_require_libs'));

Wordless::set_preference("css.lessc_path", get_option('css_lessc_path'));
if (get_option('css_compress') == 'true') Wordless::set_preference("css.compress", true);
elseif (get_option('css_compress') == 'false') Wordless::set_preference("css.compress", false);

Wordless::set_preference("js.ruby_path", get_option('js_ruby_path'));
if (get_option('js_yui_compress') == 'true') Wordless::set_preference("js.yui_compress", true);
elseif (get_option('js_yui_compress') == 'false') Wordless::set_preference("js.yui_compress", false);
if (get_option('js_yui_munge') == 'true') Wordless::set_preference("js.yui_munge", true);
elseif (get_option('js_yui_munge') == 'false') Wordless::set_preference("js.yui_munge", false);
