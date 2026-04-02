<?php
/**
 * AJAX: Save Diagnostic Frequency Override
 *
 * Stores a per-diagnostic frequency override in the
 * wpshadow_diagnostic_frequency_overrides option. Passing 'default'
 * as the frequency removes an existing override so the diagnostic
 * falls back to its class-level default.
 *
 * @package WPShadow\Admin
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save Diagnostic Frequency AJAX Handler
 */
class AJAX_Save_Diagnostic_Frequency extends AJAX_Handler_Base {

	/**
	 * Valid frequency strings.
	 *
	 * @var array<string>
	 */
	private static $valid_frequencies = array(
		'always',
		'on-change',
		'daily',
		'weekly',
		'monthly',
		'default',
	);

	/**
	 * Handle the request.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_scan_settings', 'manage_options' );

		$class_name = self::get_post_param( 'class_name', 'text', '', true );
		$frequency  = self::get_post_param( 'frequency', 'text', 'default', true );

		if ( empty( $class_name ) ) {
			self::send_error( __( 'Missing diagnostic class name.', 'wpshadow' ) );
			return;
		}

		// Validate the class exists to prevent arbitrary data injection.
		if ( ! class_exists( $class_name ) ) {
			self::send_error( __( 'Unknown diagnostic class.', 'wpshadow' ) );
			return;
		}

		// Sanitize class name to fully-qualified PHP class name characters only.
		if ( ! preg_match( '/^[a-zA-Z0-9\\\\_ ]+$/', $class_name ) ) {
			self::send_error( __( 'Invalid class name.', 'wpshadow' ) );
			return;
		}

		if ( ! in_array( $frequency, self::$valid_frequencies, true ) ) {
			self::send_error( __( 'Invalid frequency value.', 'wpshadow' ) );
			return;
		}

		$option   = 'wpshadow_diagnostic_frequency_overrides';
		$existing = get_option( $option, array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		if ( 'default' === $frequency ) {
			unset( $existing[ $class_name ] );
		} else {
			$existing[ $class_name ] = $frequency;
		}

		update_option( $option, $existing );

		self::send_success(
			array(
				'class_name' => $class_name,
				'frequency'  => $frequency,
			)
		);
	}
}

add_action(
	'wp_ajax_wpshadow_save_diagnostic_frequency',
	array( '\WPShadow\Admin\AJAX_Save_Diagnostic_Frequency', 'handle' )
);
