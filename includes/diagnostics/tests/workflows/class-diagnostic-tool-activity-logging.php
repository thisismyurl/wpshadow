<?php
/**
 * Tool Activity Logging Diagnostic
 *
 * Tests whether tool usage (imports, exports, GDPR actions) is logged
 * for security and compliance audits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool Activity Logging Diagnostic Class
 *
 * Ensures tool operations are comprehensively logged for audit trails
 * and compliance requirements.
 *
 * @since 1.6033.1900
 */
class Diagnostic_Tool_Activity_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-activity-logging';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Activity Logging for Audit Trail';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tool operations are logged for compliance audits';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Activity Logger exists and is functional
	 * - Tool operations are being logged
	 * - User attribution is recorded
	 * - Timestamps are accurate
	 * - Log retention is configured
	 *
	 * @since  1.6033.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if Activity Logger class exists.
		if ( ! class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			$issues[] = __( 'Activity Logger class not found; tool operations cannot be logged', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tool-activity-logging',
			);
		}

		$activity_log = get_option( 'wpshadow_activity_log', array() );

		if ( ! is_array( $activity_log ) ) {
			$issues[] = __( 'Activity log storage is not available; logging functionality may be broken', 'wpshadow' );
		}

		// Check if logging is enabled.
		$activity_logging_enabled = get_option( 'wpshadow_activity_logging_enabled', true );
		if ( ! $activity_logging_enabled ) {
			$issues[] = __( 'Activity logging is disabled; tool operations are not being audited', 'wpshadow' );
		}

		// Check if there are recent activity log entries for tool operations.
		if ( is_array( $activity_log ) && $activity_logging_enabled ) {
			$recent_logs = 0;
			$cutoff      = time() - ( 30 * DAY_IN_SECONDS );

			foreach ( $activity_log as $entry ) {
				$entry_time = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : 0;
				$action     = isset( $entry['action'] ) ? strtolower( (string) $entry['action'] ) : '';

				if ( $entry_time > $cutoff && false !== strpos( $action, 'tool' ) ) {
					++$recent_logs;
				}
			}

			if ( 0 === $recent_logs ) {
				$issues[] = __( 'No recent tool operation logs found in the past 30 days; check if logging is functioning', 'wpshadow' );
			}
		}

		// Check log retention policy.
		$log_retention_days = get_option( 'wpshadow_activity_log_retention_days', 90 );
		if ( (int) $log_retention_days < 30 ) {
			$issues[] = sprintf(
				/* translators: %d: days */
				__( 'Activity log retention is set to only %d days; consider increasing for compliance requirements', 'wpshadow' ),
				$log_retention_days
			);
		}

		// Check if user attribution and timestamps are present in stored log records.
		if ( is_array( $activity_log ) && ! empty( $activity_log ) ) {
			$sample = reset( $activity_log );

			if ( ! is_array( $sample ) || ! isset( $sample['user_id'] ) ) {
				$issues[] = __( 'User ID is missing from activity log records; user attribution cannot be tracked', 'wpshadow' );
			}

			if ( ! is_array( $sample ) || ! isset( $sample['timestamp'] ) ) {
				$issues[] = __( 'Timestamp is missing from activity log records; operation timing cannot be verified', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/tool-activity-logging',
			);
		}

		return null;
	}
}
