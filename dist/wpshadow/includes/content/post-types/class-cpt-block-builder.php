<?php
/**
 * CPT Visual Custom Block Builder Feature
 *
 * Provides drag-and-drop visual block building for custom post types with
 * pre-made templates, reusable components, and live preview.
 *
 * @package    WPShadow
 * @subpackage Content\Post_Types
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * CPT Block Builder Class
 *
 * Handles visual block building functionality for custom post types.
 *
 * @since 1.6093.1200
 */
class CPT_Block_Builder extends Hook_Subscriber_Base {

	/**
	 * Register WordPress hooks.
	 *
	 * @since 1.6093.1200
	 * @return array Hook configuration array.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) ),
				array( 'init', array( __CLASS__, 'register_custom_blocks' ) ),
				array( 'wp_ajax_wpshadow_save_block_template', array( __CLASS__, 'ajax_save_template' ) ),
				array( 'wp_ajax_wpshadow_get_block_templates', array( __CLASS__, 'ajax_get_templates' ) ),
			),
			'filters' => array(
				array( 'block_categories_all', array( __CLASS__, 'register_block_category' ) ),
			),
		);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function enqueue_block_editor_assets(): void {
		wp_enqueue_script(
			'wpshadow-block-builder',
			plugins_url( 'assets/js/cpt-block-builder.js', WPSHADOW_FILE ),
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-block-builder',
			'wpShadowBlockBuilder',
			array(
				'nonce'     => wp_create_nonce( 'wpshadow_block_builder' ),
				'apiUrl'    => rest_url( 'wpshadow/v1/blocks' ),
				'templates' => self::get_available_templates(),
			)
		);

		wp_enqueue_style(
			'wpshadow-block-builder',
			plugins_url( 'assets/css/post-types.css', WPSHADOW_FILE ),
			array( 'wp-edit-blocks' ),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Register custom blocks.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_custom_blocks(): void {
		register_block_type(
			'wpshadow/block-builder',
			array(
				'api_version'      => 2,
				'title'            => __( 'WPShadow Block Builder', 'wpshadow' ),
				'render_callback'  => array( __CLASS__, 'render_custom_block' ),
				'attributes'       => array(
					'content' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
	}

	/**
	 * Register block category.
	 *
	 * @since 1.6093.1200
	 * @param  array $categories Existing block categories.
	 * @return array Modified block categories.
	 */
	public static function register_block_category( array $categories ): array {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'wpshadow-cpt',
					'title' => __( ' WPShadow CPT Blocks', 'wpshadow' ),
					'icon'  => null,
				),
			)
		);
	}

	/**
	 * Render custom block.
	 *
	 * @since 1.6093.1200
	 * @param  array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public static function render_custom_block( array $attributes ): string {
		$content = $attributes['content'] ?? '';
		return '<div class="wpshadow-cpt-block">' . wp_kses_post( $content ) . '</div>';
	}

	protected static function get_required_version(): string {
		return '1.6181.2359';
	}

	/**
	 * Handle save template AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_save_template(): void {
		check_ajax_referer( 'wpshadow_block_builder', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$template_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$template_data = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';

		$templates = get_option( 'wpshadow_block_templates', array() );
		$templates[ sanitize_key( $template_name ) ] = $template_data;
		update_option( 'wpshadow_block_templates', $templates );

		wp_send_json_success( array( 'message' => __( 'Template saved successfully', 'wpshadow' ) ) );
	}

	/**
	 * Handle get templates AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_get_templates(): void {
		check_ajax_referer( 'wpshadow_block_builder', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$templates = self::get_available_templates();
		wp_send_json_success( array( 'templates' => $templates ) );
	}

	/**
	 * Get available block templates.
	 *
	 * @since 1.6093.1200
	 * @return array Available templates.
	 */
	private static function get_available_templates(): array {
		return get_option( 'wpshadow_block_templates', array() );
	}
}
