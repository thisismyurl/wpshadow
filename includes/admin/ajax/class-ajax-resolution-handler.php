<?php
/**
 * Resolution Centre AJAX Handler
 *
 * Handles two AJAX endpoints:
 *   1. wpshadow_resolution_save    — mark a diagnostic resolved / skipped / pending
 *   2. wpshadow_resolution_update_option — update a single whitelisted WP option
 *
 * @package WPShadow
 * @subpackage Admin\Ajax
 * @since 0.7000
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Resolution Centre save and option-update AJAX actions.
 */
class Ajax_Resolution_Handler extends AJAX_Handler_Base {

	/**
	 * Register both AJAX endpoints.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_resolution_save', array( self::class, 'handle_save' ) );
		add_action( 'wp_ajax_wpshadow_resolution_update_option', array( self::class, 'handle_update_option' ) );
	}

	/**
	 * Save a resolution status (resolved|skipped|pending) for a diagnostic slug.
	 *
	 * POST params: nonce, diagnostic_slug, status, note (optional)
	 */
	public static function handle_save(): void {
		self::verify_manage_options_request( 'wpshadow_resolution' );

		$slug   = self::get_post_param( 'diagnostic_slug', 'key', '', true );
		$status = self::get_post_param( 'status', 'key', '', true );
		$note   = self::get_post_param( 'note', 'textarea', '' );

		if ( ! $slug ) {
			wp_send_json_error( array( 'message' => __( 'Invalid diagnostic slug.', 'wpshadow' ) ), 400 );
		}

		$allowed_statuses = array( 'resolved', 'skipped', 'pending' );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid status.', 'wpshadow' ) ), 400 );
		}

		// Update resolution records.
		$records          = self::get_array_option( 'wpshadow_resolution_records', array() );
		$records[ $slug ] = array(
			'status'      => $status,
			'note'        => $note,
			'resolved_at' => current_time( 'mysql' ),
			'resolved_by' => get_current_user_id(),
		);
		update_option( 'wpshadow_resolution_records', $records );

		// Keep excluded-findings in sync when skipping / un-skipping.
		$excluded = self::get_array_option( 'wpshadow_excluded_findings', array() );
		if ( 'skipped' === $status ) {
			$excluded[ $slug ] = array(
				'reason'    => 'user_skipped',
				'timestamp' => time(),
				'user'      => get_current_user_id(),
			);
		} elseif ( 'pending' === $status && isset( $excluded[ $slug ] ) ) {
			unset( $excluded[ $slug ] );
		}
		update_option( 'wpshadow_excluded_findings', $excluded );

		wp_send_json_success( array( 'message' => __( 'Status saved.', 'wpshadow' ) ) );
	}

	/**
	 * Update a single whitelisted WordPress option.
	 *
	 * POST params: nonce, option_name, option_value
	 */
	public static function handle_update_option(): void {
		self::verify_manage_options_request( 'wpshadow_resolution' );

		$option_name  = self::get_post_param( 'option_name', 'key', '', true );
		$option_value = self::get_post_param( 'option_value', 'raw', '' );

		/**
		 * Whitelist of options this handler is permitted to update.
		 * Keys   = option name.
		 * Values = sanitisation type: 'bool', 'open_closed', 'update_core', 'textarea'.
		 */
		$allowed = array(
			'users_can_register'     => 'bool',
			'default_comment_status' => 'open_closed',
			'comment_moderation'     => 'bool',
			'blog_public'            => 'bool',
			'ping_sites'             => 'textarea',
			'wp_auto_update_core'    => 'update_core',
			'auto_update_core_minor' => 'bool',
			'auto_update_core_major' => 'bool',
		);

		if ( ! array_key_exists( $option_name, $allowed ) ) {
			wp_send_json_error( array( 'message' => __( 'Option not permitted.', 'wpshadow' ) ), 403 );
		}

		// Sanitise the value according to its type.
		switch ( $allowed[ $option_name ] ) {
			case 'bool':
				$clean_value = rest_sanitize_boolean( $option_value ) ? '1' : '0';
				break;
			case 'open_closed':
				$clean_value = ( 'open' === $option_value ) ? 'open' : 'closed';
				break;
			case 'update_core':
				$allowed_vals = array( 'true', 'false', 'minor-only' );
				$option_value = sanitize_text_field( (string) $option_value );
				$clean_value  = in_array( $option_value, $allowed_vals, true ) ? $option_value : 'minor-only';
				break;
			case 'textarea':
				$clean_value = sanitize_textarea_field( (string) $option_value );
				break;
			default:
				$clean_value = sanitize_text_field( (string) $option_value );
		}

		update_option( $option_name, $clean_value );

		wp_send_json_success(
			array(
				'message' => sprintf(
				/* translators: %s: option name */
					__( '%s updated.', 'wpshadow' ),
					$option_name
				),
			)
		);
	}
}
