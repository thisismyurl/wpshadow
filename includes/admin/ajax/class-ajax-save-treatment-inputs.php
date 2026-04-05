<?php
/**
 * AJAX: Save Treatment Input Requirements
 *
 * Persists user-provided input values required before specific
 * treatment/diagnostic fixes can be run safely.
 *
 * @package WPShadow
 * @since 0.6093.1400
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Treatment_Input_Requirements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save treatment input values from diagnostic detail page.
 */
class AJAX_Save_Treatment_Inputs extends AJAX_Handler_Base {
	/**
	 * Handle request.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_manage_options_request( 'wpshadow_treatment_inputs' );

		$finding_id = self::get_post_param( 'finding_id', 'key', '', true );
		$values     = self::get_post_param( 'values', 'json', array(), true );

		if ( '' === $finding_id ) {
			self::send_error( __( 'Invalid diagnostic identifier.', 'wpshadow' ) );
			return;
		}

		if ( ! is_array( $values ) ) {
			self::send_error( __( 'Invalid input payload.', 'wpshadow' ) );
			return;
		}

		$result = Treatment_Input_Requirements::sanitize_values( $finding_id, $values );
		if ( empty( $result['success'] ) ) {
			self::send_error( (string) ( $result['message'] ?? __( 'Input validation failed.', 'wpshadow' ) ) );
			return;
		}

		$normalized = isset( $result['values'] ) && is_array( $result['values'] ) ? $result['values'] : array();
		Treatment_Input_Requirements::save_values( $finding_id, $normalized );

		$applied = Treatment_Input_Requirements::apply_immediate_updates( $finding_id, $normalized );

		self::send_success(
			array(
				'message'       => (string) ( $result['message'] ?? __( 'Input requirements saved.', 'wpshadow' ) ),
				'finding_id'    => $finding_id,
				'values'        => $normalized,
				'applied'       => $applied,
				'applied_count' => count( $applied ),
			)
		);
	}
}

add_action(
	'wp_ajax_wpshadow_save_treatment_inputs',
	array( '\\WPShadow\\Admin\\Ajax\\AJAX_Save_Treatment_Inputs', 'handle' )
);
