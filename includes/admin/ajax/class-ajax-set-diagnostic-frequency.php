<?php
/**
 * AJAX: Set Diagnostic Frequency
 *
 * Stores a per-diagnostic frequency override used by Diagnostic_Scheduler.
 *
 * @package WPShadow
 * @subpackage Admin
 * @since 0.6091
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Diagnostic_Scheduler;

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
		self::verify_request( 'wpshadow_scan_settings', 'manage_options' );

		$run_key   = self::get_post_param( 'run_key', 'key', '', true );
		$frequency = (int) self::get_post_param( 'frequency', 'int', Diagnostic_Scheduler::FREQUENCY_WEEKLY, true );

		if ( '' === $run_key ) {
			self::send_error( __( 'Invalid diagnostic key.', 'wpshadow' ) );
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
\add_action( 'wp_ajax_wpshadow_set_diagnostic_frequency', array( '\WPShadow\\Admin\\AJAX_Set_Diagnostic_Frequency', 'handle' ) );
