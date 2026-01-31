<?php
/**
 * BookingPress Database Optimization Diagnostic
 *
 * BookingPress database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.463.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingPress Database Optimization Diagnostic Class
 *
 * @since 1.463.0000
 */
class Diagnostic_BookingpressDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'bookingpress-database-optimization';
	protected static $title = 'BookingPress Database Optimization';
	protected static $description = 'BookingPress database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'BOOKINGPRESS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Query optimization enabled
		$opt = get_option( 'bookingpress_query_optimization_enabled', 0 );
		if ( ! $opt ) {
			$issues[] = 'Query optimization not enabled';
		}

		// Check 2: Database indexing
		$indexing = get_option( 'bookingpress_database_indexing_enabled', 0 );
		if ( ! $indexing ) {
			$issues[] = 'Database indexing not enabled';
		}

		// Check 3: Caching enabled
		$cache = get_option( 'bookingpress_database_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Database query caching not enabled';
		}

		// Check 4: Archive old records
		$archive = get_option( 'bookingpress_archive_old_records_enabled', 0 );
		if ( ! $archive ) {
			$issues[] = 'Archive old records not enabled';
		}

		// Check 5: Cleanup logs
		$cleanup = get_option( 'bookingpress_cleanup_old_logs_enabled', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Log cleanup not enabled';
		}

		// Check 6: Table optimization schedule
		$schedule = get_option( 'bookingpress_table_optimization_schedule', '' );
		if ( empty( $schedule ) ) {
			$issues[] = 'Table optimization schedule not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d database optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bookingpress-database-optimization',
			);
		}

		return null;
	}
}
