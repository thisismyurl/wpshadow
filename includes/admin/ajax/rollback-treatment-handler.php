<?php
/**
 * Rollback Treatment Execution Handler
 *
 * Reverses previously applied treatments by restoring from backup. Gives users
 * complete confidence to apply auto-fixes knowing they can be undone.
 *
 * **Safety Features:**
 * - Backup created before every treatment application
 * - One-click undo returns site to pre-treatment state
 * - Rollback available 30 days after treatment application
 * - Full audit trail of all rollbacks recorded
 *
 * **Philosophy Alignment:**
 * - #8 (Inspire Confidence): "You can always undo this"
 * - #1 (Helpful Neighbor): Makes users feel safe experimenting
 * - #9 (Show Value): Logs rollback with reason for analytics
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handler for rolling back treatments
 *
 * Restores previous database state from backup when treatment needs reversal.
 * Handles both individual treatment rollbacks and full backup restoration.
 *
 * **Process:**
 * 1. Locate backup created at time of treatment application
 * 2. Validate backup integrity and compatibility
 * 3. Restore database tables from backup
 * 4. Verify rollback success and data integrity
 * 5. Log rollback event with reason and user
 *
 * **Related:** {@link \WPShadow\Core\Backup_Manager}
 */
class Rollback_Treatment_Handler extends AJAX_Handler_Base {
	/**
	 * Register the AJAX handler
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_rollback_treatment', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_rollback', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '' );

		if ( empty( $finding_id ) ) {
			self::send_error( __( 'Finding ID is required.', 'wpshadow' ) );
			return;
		}

		// Check if treatment can be rolled back
		if ( ! \wpshadow_can_rollback( $finding_id ) ) {
			self::send_error( __( 'This treatment does not support rollback.', 'wpshadow' ) );
			return;
		}

		// Rollback the treatment
		$result = \wpshadow_rollback_fix( $finding_id );

		if ( is_array( $result ) && ! empty( $result['success'] ) ) {
			// Log the rollback
			\WPShadow\Core\Activity_Logger::log(
				'treatment_rolled_back',
				sprintf( 'Treatment rolled back: %s', $finding_id ),
				'treatments',
				array(
					'finding_id' => $finding_id,
					'message'    => $result['message'] ?? '',
				)
			);
			self::send_success( $result );
		} else {
			self::send_error(
				$result['message'] ?? __( 'Rollback failed.', 'wpshadow' ),
				$result
			);
		}
	}
}
