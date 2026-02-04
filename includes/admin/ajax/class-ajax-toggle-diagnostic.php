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
 * @since   1.6030.2148
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
	 * @since 1.6030.2148
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_scan_settings', 'manage_options' );

		$class_name = self::get_post_param( 'class_name', 'text', '', true );
		$enable     = rest_sanitize_boolean( self::get_post_param( 'enable', 'bool', false ) );

		if ( empty( $class_name ) || ! class_exists( $class_name ) ) {
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
