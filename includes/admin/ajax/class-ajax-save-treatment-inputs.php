<?php
/**
 * AJAX: Save Treatment Input Requirements
 *
 * Persists user-provided input values required before specific
 * treatment/diagnostic fixes can be run safely.
 *
 * @package ThisIsMyURL\Shadow
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Admin\Ajax;

use ThisIsMyURL\Shadow\Core\AJAX_Handler_Base;
use ThisIsMyURL\Shadow\Core\Treatment_Input_Requirements;

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
		self::verify_manage_options_request( 'thisismyurl_shadow_treatment_inputs' );

		$finding_id = self::get_post_param( 'finding_id', 'key', '', true );
		$values     = self::get_post_param( 'values', 'json', array(), true );

		if ( '' === $finding_id ) {
			self::send_error( __( 'Invalid diagnostic identifier.', 'thisismyurl-shadow' ) );
			return;
		}

		if ( ! is_array( $values ) ) {
			self::send_error( __( 'Invalid input payload.', 'thisismyurl-shadow' ) );
			return;
		}

		$result = Treatment_Input_Requirements::sanitize_values( $finding_id, $values );
		if ( empty( $result['success'] ) ) {
			self::send_error( (string) ( $result['message'] ?? __( 'Input validation failed.', 'thisismyurl-shadow' ) ) );
			return;
		}

		$normalized = isset( $result['values'] ) && is_array( $result['values'] ) ? $result['values'] : array();
		Treatment_Input_Requirements::save_values( $finding_id, $normalized );

		$applied = Treatment_Input_Requirements::apply_immediate_updates( $finding_id, $normalized );

		self::send_success(
			array(
				'message'       => (string) ( $result['message'] ?? __( 'Input requirements saved.', 'thisismyurl-shadow' ) ),
				'finding_id'    => $finding_id,
				'values'        => $normalized,
				'applied'       => $applied,
				'applied_count' => count( $applied ),
			)
		);
	}
}

add_action(
	'wp_ajax_thisismyurl_shadow_save_treatment_inputs',
	array( '\\ThisIsMyURL\\Shadow\\Admin\\Ajax\\AJAX_Save_Treatment_Inputs', 'handle' )
);
