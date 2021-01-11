<?php

class WordlessPugOptions {
    public static function get_options() {
        $wp_debug = defined('WP_DEBUG') ? WP_DEBUG : false;
        return apply_filters( 'wordless_pug_configuration', [
            'expressionLanguage' => 'php',
            'extension' => '.pug',
            'cache' => Wordless::theme_temp_path(),
            'strict' => true,
            'debug' => $wp_debug,
            'enable_profiler' => false,
            'error_reporting' => E_ERROR | E_USER_ERROR,
            'keep_base_name' => true,
            'paths' => [Wordless::theme_views_path()],
            'mixin_keyword' => ['mixin','component'],
        ]);
    }
}
