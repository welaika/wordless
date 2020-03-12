<?php
/**
 * This module provides methods to register a Gutenberg Block
 * to be selected within ACF Field Group options, in the Rules panel.
 *
 * @ingroup helperclass
 */
class AcfGutenbergBlockHelper {

  /**
   * Creates and registers ACF Gutenberg block
   *
   * @param string $block_name
   *    The name of the block.
   *
   * @param array $params
   *    The array of block specific params that will
   *    override the defaults.
   *
   * @ingroup helperfunc
   */

  function create_acf_block(string $block_name, array $params) {
    if ( !function_exists('acf_register_block') )
      return;

    $params = array_merge(array(
      'name'              => $block_name,
      'title'             => __($block_name),
      'description'       => __($block_name),
      'render_callback'   => "_acf_block_render_callback",
      'category'          => 'formatting',
      'icon'              => 'smiley',
      'keywords'          => ['acf', 'block'],
    ), $params);


    acf_register_block( $params );
  }


  /**
   * NO_DOC: This function is not meant to be used in a theme.
   * It needs to be public because of the implementation
   * of ACF function acf_register_block.
   * If set as private, ACF loses the context and is not able to call it.
   */
  function _acf_block_render_callback( $block ) {
    $slug = str_replace('acf/', '', $block['name']);

    // The filter must return a string, representing a folder relative to `views/`
    $blocks_folder = apply_filters('wordless_acf_gutenberg_blocks_views_path', 'blocks/');

    $admin_partial_filename = Wordless::theme_views_path() . "/{$blocks_folder}/admin/_{$slug}";

    if (
      file_exists( "{$admin_partial_filename}.html.pug" ) ||
      file_exists( "{$admin_partial_filename}.pug" ) ||
      file_exists( "{$admin_partial_filename}.html.php" ) ||
      file_exists( "{$admin_partial_filename}.php" )
    ) {
        $admin_partial = "{$blocks_folder}/admin/{$slug}";
    } else {
        $admin_partial = "{$blocks_folder}/{$slug}";
    }

    if ( is_admin() ) {
        render_partial($admin_partial);
    } else {
        render_partial("{$blocks_folder}/{$slug}");
    }
  }
}

Wordless::register_helper("AcfGutenbergBlockHelper");
