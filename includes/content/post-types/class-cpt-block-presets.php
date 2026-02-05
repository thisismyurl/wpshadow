<?php
/**
 * CPT Block Presets
 *
 * Provides saved block presets/configurations for custom post types,
 * allowing users to save and reuse block settings.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.6181.2359
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Block_Presets Class
 *
 * Manages saved block configurations and presets.
 *
 * @since 1.6181.2359
 */
class CPT_Block_Presets {

	/**
	 * Initialize block presets system.
	 *
	 * @since 1.6034.1330
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_preset_post_type' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'wp_ajax_wpshadow_save_preset', array( __CLASS__, 'handle_save_preset' ) );
		add_action( 'wp_ajax_wpshadow_load_preset', array( __CLASS__, 'handle_load_preset' ) );
		add_action( 'wp_ajax_wpshadow_delete_preset', array( __CLASS__, 'handle_delete_preset' ) );
	}

	/**
	 * Register block presets post type.
	 *
	 * @since 1.6034.1330
	 * @return void
	 */
	public static function register_preset_post_type() {
		register_post_type(
			'wps_block_preset',
			array(
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => false,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'capability_type'     => 'post',
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
			)
		);
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @since 1.6034.1330
	 * @return void
	 */
	public static function enqueue_editor_assets() {
		$screen = get_current_screen();
		
		// Only load on post editor screens.
		if ( ! $screen || ! in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			return;
		}

		// Verify post type exists.
		if ( ! post_type_exists( $screen->post_type ) ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-block-presets',
			WPSHADOW_URL . 'assets/js/cpt-block-presets.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-block-presets',
			'wpShadowPresets',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_block_presets' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'presets' => self::get_user_presets(),
			)
		);
	}

	/**
	 * Handle save preset AJAX request.
	 *
	 * @since 1.6034.1330
	 * @return void
	 */
	public static function handle_save_preset() {
		check_ajax_referer( 'wpshadow_block_presets', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$block_name = isset( $_POST['block_name'] ) ? sanitize_text_field( wp_unslash( $_POST['block_name'] ) ) : '';
		$attributes = isset( $_POST['attributes'] ) ? wp_unslash( $_POST['attributes'] ) : '';

		if ( empty( $name ) || empty( $block_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Name and block name are required', 'wpshadow' ) ) );
		}

		$preset_id = wp_insert_post(
			array(
				'post_type'   => 'wps_block_preset',
				'post_title'  => $name,
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
			)
		);

		if ( is_wp_error( $preset_id ) ) {
			wp_send_json_error( array( 'message' => $preset_id->get_error_message() ) );
		}

		update_post_meta( $preset_id, '_block_name', $block_name );
		update_post_meta( $preset_id, '_attributes', $attributes );

		wp_send_json_success( array(
			'message'   => __( 'Preset saved successfully', 'wpshadow' ),
			'preset_id' => $preset_id,
		) );
	}

	/**
	 * Handle load preset AJAX request.
	 *
	 * @since 1.6034.1330
	 * @return void
	 */
	public static function handle_load_preset() {
		check_ajax_referer( 'wpshadow_block_presets', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$preset_id = isset( $_POST['preset_id'] ) ? absint( $_POST['preset_id'] ) : 0;

		if ( ! $preset_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid preset ID', 'wpshadow' ) ) );
		}

		$preset = get_post( $preset_id );

		if ( ! $preset || 'wps_block_preset' !== $preset->post_type ) {
			wp_send_json_error( array( 'message' => __( 'Preset not found', 'wpshadow' ) ) );
		}

		$block_name = get_post_meta( $preset_id, '_block_name', true );
		$attributes = get_post_meta( $preset_id, '_attributes', true );

		wp_send_json_success( array(
			'block_name' => $block_name,
			'attributes' => $attributes,
		) );
	}

	/**
	 * Handle delete preset AJAX request.
	 *
	 * @since 1.6034.1330
	 * @return void
	 */
	public static function handle_delete_preset() {
		check_ajax_referer( 'wpshadow_block_presets', 'nonce' );

		if ( ! current_user_can( 'delete_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$preset_id = isset( $_POST['preset_id'] ) ? absint( $_POST['preset_id'] ) : 0;

		if ( ! $preset_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid preset ID', 'wpshadow' ) ) );
		}

		$result = wp_delete_post( $preset_id, true );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete preset', 'wpshadow' ) ) );
		}

		wp_send_json_success( array(
			'message' => __( 'Preset deleted successfully', 'wpshadow' ),
		) );
	}

	/**
	 * Get user's saved presets.
	 *
	 * @since  1.6034.1330
	 * @return array Array of presets.
	 */
	private static function get_user_presets() {
		$presets = get_posts(
			array(
				'post_type'      => 'wps_block_preset',
				'posts_per_page' => -1,
				'author'         => get_current_user_id(),
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$formatted_presets = array();

		foreach ( $presets as $preset ) {
			$formatted_presets[] = array(
				'id'         => $preset->ID,
				'name'       => $preset->post_title,
				'block_name' => get_post_meta( $preset->ID, '_block_name', true ),
			);
		}

		return $formatted_presets;
	}
}
