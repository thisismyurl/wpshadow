<?php
/**
 * Autofix Finding AJAX Handler
 *
 * One-click automated fix trigger from dashboard. When user clicks "Fix Now" button,
 * this handler executes the appropriate treatment and reports results.
 *
 * **User Flow:**
 * 1. Dashboard shows finding with "Fix Now" button
 * 2. User clicks button
 * 3. This handler triggers treatment via AJAX
 * 4. Optional: Show dry-run first (preview changes)
 * 5. Result: Success message or error explanation
 *
 * **Philosophy Alignment:**
 * - #8 (Inspire Confidence): Clear before/after feedback
 * - #1 (Helpful Neighbor): Error messages explain why fix failed
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Autofix_Finding_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hooks for one-click finding auto-fixes.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_autofix_finding', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle one-click finding auto-fix requests.
	 *
	 * @since 0.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_autofix', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '' );
		$result     = \wpshadow_attempt_autofix( $finding_id );

		if ( is_array( $result ) && ! empty( $result['success'] ) ) {
			// Log the fix
			\WPShadow\Core\Activity_Logger::log(
				'treatment_applied',
				sprintf( 'Auto-fix applied: %s', $finding_id ),
				'workflows',
				array(
					'finding_id' => $finding_id,
					'message'    => $result['message'] ?? '',
				)
			);
			self::send_success( $result );
		} else {
			self::send_error( $result['message'] ?? __( 'Auto-fix failed.', 'wpshadow' ), $result );
		}
	}
}
