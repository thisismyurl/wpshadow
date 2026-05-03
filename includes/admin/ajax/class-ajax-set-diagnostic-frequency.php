<?php
/**
 * AJAX: Set Diagnostic Frequency
 *
 * Stores a per-diagnostic frequency override used by Diagnostic_Scheduler.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Admin
 * @since 0.6091
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Admin;

use ThisIsMyURL\Shadow\Core\AJAX_Handler_Base;
use ThisIsMyURL\Shadow\Core\Diagnostic_Scheduler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update diagnostic frequency handler.
 *
 * @since 0.6091
 */
class AJAX_Set_Diagnostic_Frequency extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6091
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'thisismyurl_shadow_scan_settings', 'manage_options' );

		$run_key   = self::get_post_param( 'run_key', 'key', '', true );
		$frequency = (int) self::get_post_param( 'frequency', 'int', Diagnostic_Scheduler::FREQUENCY_WEEKLY, true );

		if ( '' === $run_key ) {
			self::send_error( __( 'Invalid diagnostic key.', 'thisismyurl-shadow' ) );
		}

		$stored_frequency = Diagnostic_Scheduler::set_frequency_override( $run_key, $frequency );

		self::send_success(
			array(
				'run_key'   => $run_key,
				'frequency' => $stored_frequency,
			)
		);
	}
}

// Register AJAX action.
\add_action( 'wp_ajax_thisismyurl_shadow_set_diagnostic_frequency', array( '\ThisIsMyURL\\Shadow\\Admin\\AJAX_Set_Diagnostic_Frequency', 'handle' ) );
