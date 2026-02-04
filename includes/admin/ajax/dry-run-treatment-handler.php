<?php
/**
 * Dry Run Treatment Execution Handler
 *
 * Executes treatments in "preview mode" without making permanent changes. Shows users
 * exactly what would happen before committing to the fix.
 *
 * **Why Dry-Run Matters:**
 * - User sees before/after state without risk
 * - Builds confidence in auto-fix system
 * - Identifies potential issues before permanent changes
 * - Enables informed decision-making
 *
 * **Philosophy Alignment:**
 * - #8 (Inspire Confidence): "See what we'd do before we do it"
 * - #1 (Helpful Neighbor): "Here's the impact... ready to proceed?"
 *
 * @package WPShadow
 * @since 1.603.1345
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handler for dry-run treatment execution
 *
 * Simulates treatment application without persistent changes.
 * Returns detailed report of what treatment would modify.
 *
 * **Response Includes:**
 * - List of files/settings that would change
 * - Before/after values for each modification
 * - Estimated impact metrics
 * - Reversibility/rollback information
 */
class Dry_Run_Treatment_Handler extends AJAX_Handler_Base {
	/**
	 * Register the AJAX handler
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dry_run_treatment', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_dry_run', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '' );

		if ( empty( $finding_id ) ) {
			self::send_error( __( 'Finding ID is required.', 'wpshadow' ) );
			return;
		}

		// Run treatment in dry-run mode
		$result = \wpshadow_attempt_autofix( $finding_id, true );

		if ( is_array( $result ) && ! empty( $result['success'] ) ) {
			// Log the dry run
			\WPShadow\Core\Activity_Logger::log(
				'treatment_dry_run',
				sprintf( 'Dry run completed: %s', $finding_id ),
				'treatments',
				array(
					'finding_id'  => $finding_id,
					'would_apply' => $result['would_apply'] ?? false,
					'message'     => $result['message'] ?? '',
				)
			);
			self::send_success( $result );
		} else {
			self::send_error(
				$result['message'] ?? __( 'Dry run failed.', 'wpshadow' ),
				$result
			);
		}
	}
}
