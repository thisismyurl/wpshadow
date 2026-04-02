<?php
/**
 * AJAX: Toggle Diagnostic Enable/Disable State
 *
 * Allows users to enable/disable specific diagnostics in scan without page reload.
 * Disabled diagnostics won't run during automated scans or manual runs.
 *
 * **Use Case:**
 * - User finds a diagnostic that doesn't apply to them → disable it
 * - Reduces scan noise and time
 * - Gets relevant results faster
 * - Can re-enable anytime in settings
 *
 * **Philosophy Alignment:**
 * - #1 (Helpful Neighbor): "Customize your scans to what matters to you"
 * - #7 (Ridiculously Good): Instant toggle, no page reload
 * - #8 (Inspire Confidence): Clear on/off state shown
 *
 * @since 0.6093.1200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Toggle Diagnostic Handler
 *
 * Updates diagnostic enabled/disabled state in user settings.
 * Respected by scan engine - disabled diagnostics skipped.
 *
 * **Request:**
 * - `diagnostic_id`: Diagnostic slug
 * - `enabled`: Boolean true/false
 *
 * **Response:**
 * - `success`: Boolean
 * - `data.enabled`: New state
 * - `data.total_enabled`: Count of enabled diagnostics
 */
class AJAX_Toggle_Diagnostic extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_scan_settings', 'manage_options' );

		// Read the class name directly — get_post_param uses wp_unslash/stripslashes which
		// strips namespace separator backslashes (e.g. WPShadow\Diagnostics\Foo → WPShadowDiagnosticsFoo).
		$class_name = '';
		if ( isset( $_POST['class_name'] ) ) {
			$raw = (string) $_POST['class_name'];
			// Validate it looks like a PHP class / fully-qualified name (letters, digits, underscore, backslash).
			if ( preg_match( '/^[a-zA-Z_\\\\][a-zA-Z0-9_\\\\]*$/', $raw ) ) {
				$class_name = $raw;
			}
		}
		$enable = rest_sanitize_boolean( self::get_post_param( 'enable', 'bool', false ) );

		if ( empty( $class_name ) ) {
			self::send_error( esc_html__( 'Invalid diagnostic class', 'wpshadow' ) );
			return;
		}

		// Ensure the diagnostic class file is loaded (it may not be autoloaded during AJAX).
		if ( ! class_exists( $class_name ) && class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
			$file_map   = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();
			$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $class_name );

			foreach ( array( $class_name, $short_name ) as $candidate ) {
				if ( isset( $file_map[ $candidate ]['file'] ) ) {
					$file = (string) $file_map[ $candidate ]['file'];
					if ( '' !== $file && file_exists( $file ) ) {
						require_once $file;
					}
				}
			}
		}

		if ( ! class_exists( $class_name ) ) {
			self::send_error( esc_html__( 'Invalid diagnostic class', 'wpshadow' ) );
			return;
		}

		$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		$disabled = is_array( $disabled ) ? $disabled : array();

		if ( $enable ) {
			// Remove from disabled
			$disabled = array_values(
				array_filter(
					$disabled,
					function ( $c ) use ( $class_name ) {
						return $c !== $class_name;
					}
				)
			);
		} else {
			// Add to disabled if not present
			if ( ! in_array( $class_name, $disabled, true ) ) {
				$disabled[] = $class_name;
			}
		}

		update_option( 'wpshadow_disabled_diagnostic_classes', $disabled );

		self::send_success(
			array(
				'class_name' => $class_name,
				'enabled'    => $enable,
			)
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_toggle_diagnostic', array( '\WPShadow\\Admin\\AJAX_Toggle_Diagnostic', 'handle' ) );
