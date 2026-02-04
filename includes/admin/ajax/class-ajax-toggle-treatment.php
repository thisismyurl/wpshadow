<?php
/**
 * AJAX: Toggle Treatment Enable/Disable State
 *
 * Handles real-time toggle of treatment enablement via admin dashboard AJAX.
 * Allows users to enable/disable auto-fix treatments without page reload.
 *
 * **User Experience:**
 * - One-click toggle switches treatment state instantly in UI
 * - No page refresh required - immediate visual feedback
 * - Disabled treatments won't run during automated scans
 * - User choice persists across sessions and site updates
 *
 * **Philosophy Alignment:**
 * - #7 (Ridiculously Good for Free): Smooth UX with instant toggle feedback
 * - #8 (Inspire Confidence): Clear on/off state shown to user at all times
 * - #1 (Helpful Neighbor): Asks "are you sure?" before disabling critical fixes
 *
 * **Related Features:**
 * - Treatment execution system (respects user preferences)
 * - Treatment scanning workflow (honors disabled states)
 * - Admin dashboard UI (toggles update visibly)
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
 * Toggle Treatment Handler
 *
 * Manages toggling treatment enabled/disabled state via AJAX.
 * Validates permission, updates option, and returns current state.
 *
 * **Request Parameters:**
 * - `treatment_id` (required): Treatment identifier slug
 * - `enabled` (required): Boolean or string 'true'/'false'
 * - `nonce`: WordPress nonce for CSRF protection
 *
 * **Response Format:**
 * ```json
 * {
 *   "success": true,
 *   "message": "Treatment disabled",
 *   "data": {
 *     "treatment_id": "database-cleanup",
 *     "enabled": false,
 *     "previous_state": true
 *   }
 * }
 * ```
 */
class AJAX_Toggle_Treatment extends AJAX_Handler_Base {
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

		if ( empty( $class_name ) ) {
			self::send_error( esc_html__( 'Invalid treatment class', 'wpshadow' ) );
			return;
		}

		$disabled = get_option( 'wpshadow_disabled_treatment_classes', array() );
		$disabled = is_array( $disabled ) ? $disabled : array();

		if ( $enable ) {
			$disabled = array_values(
				array_filter(
					$disabled,
					function ( $c ) use ( $class_name ) {
						return $c !== $class_name;
					}
				)
			);
		} elseif ( ! in_array( $class_name, $disabled, true ) ) {
				$disabled[] = $class_name;
		}

		update_option( 'wpshadow_disabled_treatment_classes', $disabled );

		self::send_success(
			array(
				'class_name' => $class_name,
				'enabled'    => $enable,
			)
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_toggle_treatment', array( '\WPShadow\\Admin\\AJAX_Toggle_Treatment', 'handle' ) );
