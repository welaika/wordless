<?php

function custom_gutenberg_acf_blocks() {
    /*
     * Create Gutenberg Block with Advanced Custom Fields.
     * Note: You can reapeat it for as many blocks as you have to create
     * Params:
     *     mandatory:
     *.       block name
     *     optional:
              array of params:
     *        title           => if blank use $block_name
     *        description     => if blank use $block_name
     *        category        => if blank use 'formatting'
     *        icon            => if blank use 'smiley'
     *        render_callback => if blank use '_acf_block_render_callback',
     *        keywords        => if blank use ['acf', 'block']
     *
     */

    // create_acf_block('slider', ['title' => 'Slider', 'description' => 'Slider', 'category' => 'custom blocks', 'icon' => 'admin-comments', 'render_callback' => '_acf_block_render_callback', 'keywords' => [ 'image', 'slider' ]]);
}

add_action('acf/init', 'custom_gutenberg_acf_blocks');
