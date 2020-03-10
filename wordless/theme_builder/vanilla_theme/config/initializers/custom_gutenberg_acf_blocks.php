<?php

function custom_gutenberg_acf_blocks() {
    /*
     * Create Gutenberg Block with Advanced Custom Fields.
     * This function is a wrapper around the `acf_register_block` function. Read more about it at
     * https://www.advancedcustomfields.com/blog/acf-5-8-introducing-acf-blocks-for-gutenberg/
     *
     * Note: You can reapeat it for as many blocks as you have to create
     *
     * Params:
     *     string, mandatory:
     *         "block name"; if you use spaces in the name, they'll get converted to `-`
                where needed. You'll need to name your partial the same as this param.
                E.g.: having "Home Page" Wordless will search for `views/blocks/home-page.html.pug`
                partial
     *     array, optional:
     *        title           => if blank use $block_name
     *        description     => if blank use $block_name
     *        category        => if blank use 'formatting'.
                                 Default categories are:
                                    'common',
                                    'formatting',
                                    'widgets',
                                    'layout',
                                    'embed'.
     *        icon            => if blank use 'smiley'; you can use any icon name from
     *                           https://developer.wordpress.org/resource/dashicons/
     *        render_callback => if blank use the default '_acf_block_render_callback',
     *        keywords        => if blank use ['acf', 'block']
     *
     */

    /* Example:
    create_acf_block('slider', [
        'title' => 'Slider',
        'description' => 'Slider',
        'category' => 'widgets',
        'icon' => 'admin-comments',
        'render_callback' => '_acf_block_render_callback',
        'keywords' => [ 'image', 'slider' ]
    ]);
    */

    // create_acf_block('slider', array());
}

add_action('acf/init', 'custom_gutenberg_acf_blocks');
