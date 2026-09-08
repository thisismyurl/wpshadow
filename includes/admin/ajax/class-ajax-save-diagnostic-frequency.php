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
 * @since 0.6095
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
		'disabled',
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

		// Sanitize class name to fully-qualified PHP class name characters only.
		if ( ! preg_match( '/^[a-zA-Z0-9\\\\_ ]+$/', $class_name ) ) {
			self::send_error( __( 'Invalid class name.', 'wpshadow' ) );
			return;
		}

		// Validate the class is a known diagnostic by checking the registry file map.
		$is_known = false;
		if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			$file_map = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();
			foreach ( $file_map as $short_class => $data ) {
				$fq = 0 === strpos( $short_class, 'WPShadow\\Diagnostics\\' )
					? $short_class
					: 'WPShadow\\Diagnostics\\' . $short_class;
				if ( $fq === $class_name ) {
					$is_known = true;
					// Ensure the class is loaded so future class_exists checks pass.
					if ( ! class_exists( $class_name ) && ! empty( $data['file'] ) && file_exists( $data['file'] ) ) {
						require_once $data['file'];
					}
					break;
				}
			}
		}

		if ( ! $is_known ) {
			self::send_error( __( 'Unknown diagnostic class.', 'wpshadow' ) );
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

		$enabled = 'disabled' !== $frequency;
		self::toggle_class_in_disabled_list( 'wpshadow_disabled_diagnostic_classes', $class_name, $enabled );

		if ( 'default' === $frequency ) {
			unset( $existing[ $class_name ] );
		} elseif ( 'disabled' !== $frequency ) {
			$existing[ $class_name ] = $frequency;
		}

		update_option( $option, $existing );

		$message = 'disabled' === $frequency
			? __( 'Diagnostic disabled. Choose another schedule later to re-enable it.', 'wpshadow' )
			: ( 'default' === $frequency
				? __( 'Schedule reset to this diagnostic default.', 'wpshadow' )
				: __( 'Schedule saved. Future runs will follow this setting.', 'wpshadow' ) );

		self::send_success(
			array(
				'class_name' => $class_name,
				'frequency'  => $frequency,
				'enabled'    => $enabled,
				'message'    => $message,
			)
		);
	}
}

add_action(
	'wp_ajax_wpshadow_save_diagnostic_frequency',
	array( '\WPShadow\Admin\AJAX_Save_Diagnostic_Frequency', 'handle' )
);
