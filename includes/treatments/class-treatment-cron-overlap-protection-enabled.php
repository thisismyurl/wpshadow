<?php
/**
 * Treatment: Clear Stale WP-Cron Lock
 *
 * Deletes the doing_cron option when it has been stale for more than
 * 10 minutes. A stale lock prevents WordPress from spawning new cron runs,
 * causing all scheduled tasks to stall. Clearing it allows cron to resume.
 *
 * Risk level: safe — only deletes the option when the lock is demonstrably
 * stale (age > 10 minutes). No impact on healthy, in-progress cron runs.
 *
 * Undo: not applicable — cron will re-set its own lock on the next run.
 *
 * @package WPShadow
 * @since   0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clears the stale doing_cron lock to unblock scheduled tasks.
 */
class Treatment_Cron_Overlap_Protection_Enabled extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'cron-overlap-protection-enabled';

	/**
	 * Stale threshold in seconds (10 minutes).
	 */
	private const STALE_THRESHOLD = 600;

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Delete the stale doing_cron option to allow cron to resume.
	 *
	 * @return array
	 */
	public static function apply() {
		$lock = (float) get_option( 'doing_cron', 0 );

		if ( $lock > 0 && ( microtime( true ) - $lock ) < self::STALE_THRESHOLD ) {
			return array(
				'success' => false,
				'message' => __( 'The cron lock was set less than 10 minutes ago and may still be in use. No changes made.', 'wpshadow' ),
			);
		}

		$deleted = delete_option( 'doing_cron' );

		if ( ! $deleted ) {
			return array(
				'success' => false,
				'message' => __( 'Could not delete the doing_cron option. It may have already been cleared.', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Stale cron lock (doing_cron) cleared. WordPress scheduled tasks can now resume. Review PHP error logs for the cause of the original cron crash.', 'wpshadow' ),
			'details' => array(
				'lock_age_seconds' => $lock > 0 ? (int) ( microtime( true ) - $lock ) : null,
			),
		);
	}

	/**
	 * Undo is not applicable — cron manages its own lock.
	 *
	 * @return array
	 */
	public static function undo() {
		return array(
			'success' => false,
			'message' => __( 'The cron lock is managed automatically by WordPress and cannot be manually restored.', 'wpshadow' ),
		);
	}
}
