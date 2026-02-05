<?php
/**
 * CPT Visual Custom Block Builder Feature
 *
 * Provides drag-and-drop visual block building for custom post types with
 * pre-made templates, reusable components, and live preview.
 *
 * @package    WPShadow
 * @subpackage Content\Post_Types
 * @since      1.6181.2359
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

use WPShadow\Systems\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * CPT Block Builder Class
 *
 * Handles visual block building functionality for custom post types.
 *
 * @since 1.6181.2359
 */
class CPT_Block_Builder extends Hook_Subscriber_Base {

/**
 * Register WordPress hooks.
 *
 * @since  1.6035.1400
 * @return array Hook configuration array.
 */
protected static function get_hooks(): array {
 array(
s' => array(
( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) ),
( 'init', array( __CLASS__, 'register_custom_blocks' ) ),
( 'wp_ajax_wpshadow_save_block_template', array( __CLASS__, 'ajax_save_template' ) ),
( 'wp_ajax_wpshadow_get_block_templates', array( __CLASS__, 'ajax_get_templates' ) ),
=> array(
( 'block_categories_all', array( __CLASS__, 'register_block_category' ) ),

/**
 * Enqueue block editor assets.
 *
 * @since 1.6035.1400
 * @return void
 */
public static function enqueue_block_editor_assets(): void {
queue_script(
s_url( 'assets/js/cpt-block-builder.js', WPSHADOW_FILE ),
( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data' ),
,

(
once'     => wp_create_nonce( 'wpshadow_block_builder' ),
   => rest_url( 'wpshadow/v1/blocks' ),
=> self::get_available_templates(),

queue_style(
s_url( 'assets/css/cpt-block-builder.css', WPSHADOW_FILE ),
( 'wp-edit-blocks' ),


/**
 * Register custom blocks.
 *
 * @since 1.6035.1400
 * @return void
 */
public static function register_custom_blocks(): void {
pe(
(
  => 'wpshadow-block-builder',
le'    => 'wpshadow-block-builder',
der_callback' => array( __CLASS__, 'render_custom_block' ),
     => array(
tent' => array(
pe'    => 'string',
=> '',

/**
 * Register block category.
 *
 * @since  1.6035.1400
 * @param  array $categories Existing block categories.
 * @return array Modified block categories.
 */
public static function register_block_category( array $categories ): array {
 array_merge(
(
(
 => 'wpshadow-cpt',
=> __( 'WPShadow CPT Blocks', 'wpshadow' ),
'  => null,

/**
 * Render custom block.
 *
 * @since  1.6035.1400
 * @param  array $attributes Block attributes.
 * @return string Rendered block HTML.
 */
public static function render_custom_block( array $attributes ): string {
tent = $attributes['content'] ?? '';
 '<div class="wpshadow-cpt-block">' . wp_kses_post( $content ) . '</div>';
}

	protected static function get_required_version(): string {
		return '1.6181.2359';
	}

/**
 * Handle save template AJAX request.
 *
 * @since 1.6035.1400
 * @return void Dies after sending JSON response.
 */
public static function ajax_save_template(): void {
'wpshadow_block_builder', 'nonce' );

( ! current_user_can( 'edit_posts' ) ) {
d_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );

ame = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
= isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';

= get_option( 'wpshadow_block_templates', array() );
sanitize_key( $template_name ) ] = $template_data;
( 'wpshadow_block_templates', $templates );

d_json_success( array( 'message' => __( 'Template saved successfully', 'wpshadow' ) ) );
}

/**
 * Handle get templates AJAX request.
 *
 * @since 1.6035.1400
 * @return void Dies after sending JSON response.
 */
public static function ajax_get_templates(): void {
'wpshadow_block_builder', 'nonce' );

( ! current_user_can( 'edit_posts' ) ) {
d_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );

= self::get_available_templates();
d_json_success( array( 'templates' => $templates ) );
}

/**
 * Get available block templates.
 *
 * @since  1.6035.1400
 * @return array Available templates.
 */
private static function get_available_templates(): array {
 get_option( 'wpshadow_block_templates', array() );
}
}
