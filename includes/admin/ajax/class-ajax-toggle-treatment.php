<?php
/**
 * AJAX: Toggle Treatment (enable/disable)
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
 * Toggle Treatment Handler
 */
class AJAX_Toggle_Treatment extends AJAX_Handler_Base {
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
