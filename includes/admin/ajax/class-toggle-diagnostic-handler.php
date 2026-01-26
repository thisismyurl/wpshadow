<?php
/**
 * AJAX: Toggle Diagnostic (enable/disable)
 *
 * @since   1.2601.2148
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
 */
class AJAX_Toggle_Diagnostic extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.2601.2148
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_scan_settings', 'manage_options' );

		$class_name = self::get_post_param( 'class_name', 'text', '', true );
		$enable     = rest_sanitize_boolean( self::get_post_param( 'enable', 'bool', false ) );

		if ( empty( $class_name ) || ! class_exists( $class_name ) ) {
			self::send_error( esc_html__( 'Invalid diagnostic class', 'wpshadow' ) );
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
