<?php
/**
 * Activity Logging Disabled or Sparse Diagnostic
 *
 * Tests for activity logging configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
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
 * @since 1.26033.0000
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
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if activity logging is enabled.
		$logging_enabled = get_option( '_wpshadow_activity_logging_enabled' );

		if ( empty( $logging_enabled ) ) {
			$issues[] = __( 'Activity logging is not enabled - cannot track changes', 'wpshadow' );
		}

		// Check activity log table.
		$log_table = $wpdb->prefix . 'wpshadow_activity_log';
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$log_table}'" );

		if ( empty( $table_exists ) ) {
			$issues[] = __( 'Activity log table does not exist - logging not properly configured', 'wpshadow' );
		} else {
			// Check if table has recent entries.
			$recent_entries = $wpdb->get_var( "SELECT COUNT(*) FROM {$log_table} WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)" );

			if ( (int) $recent_entries === 0 ) {
				$issues[] = __( 'No recent activity log entries - logging may be inactive', 'wpshadow' );
			}

			// Check if table is getting too large.
			$entry_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$log_table}" );

			if ( $entry_count > 100000 ) {
				$issues[] = sprintf(
					/* translators: %d: number of log entries */
					__( '%d activity log entries - table size may impact performance', 'wpshadow' ),
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
				'kb_link'      => 'https://wpshadow.com/kb/activity-logging-disabled-or-sparse',
			);
		}

		return null;
	}
}
