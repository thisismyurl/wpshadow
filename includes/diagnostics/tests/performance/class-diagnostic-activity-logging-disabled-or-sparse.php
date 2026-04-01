<?php
/**
 * Activity Logging Disabled or Sparse Diagnostic
 *
 * Tests for activity logging configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activity Logging Disabled or Sparse Diagnostic Class
 *
 * Tests for activity logging configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Activity_Logging_Disabled_Or_Sparse extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'activity-logging-disabled-or-sparse';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Activity Logging Disabled or Sparse';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for activity logging configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if activity logging is enabled.
		$logging_enabled = get_option( '_wpshadow_activity_logging_enabled' );

		if ( empty( $logging_enabled ) ) {
			$issues[] = __( 'Activity logging is not enabled - cannot track changes', 'wpshadow' );
		}

		$activity_log = get_option( 'wpshadow_activity_log', array() );

		if ( ! is_array( $activity_log ) ) {
			$issues[] = __( 'Activity log data is unavailable - logging not properly configured', 'wpshadow' );
		} else {
			$recent_entries = 0;
			$entry_count    = count( $activity_log );
			$cutoff         = time() - DAY_IN_SECONDS;

			foreach ( $activity_log as $entry ) {
				$entry_time = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : 0;
				if ( $entry_time > $cutoff ) {
					++$recent_entries;
				}
			}

			if ( 0 === $recent_entries ) {
				$issues[] = __( 'No recent activity log entries - logging may be inactive', 'wpshadow' );
			}

			if ( $entry_count > 100000 ) {
				$issues[] = sprintf(
					/* translators: %d: number of log entries */
					__( '%d activity log entries - log size may impact performance', 'wpshadow' ),
					$entry_count
				);
			}
		}

		// Check if WP_DEBUG_LOG is enabled for comprehensive logging.
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			$issues[] = __( 'WP_DEBUG_LOG not enabled - errors won\'t be logged for debugging', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/activity-logging-disabled-or-sparse?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
