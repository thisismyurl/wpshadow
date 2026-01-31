<?php
/**
 * Database Auto-Repair Frequency
 *
 * Checks if WordPress database is auto-repairing frequently, which signals
 * potential data corruption or hardware issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Database
 * @since      1.6028.1049
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Auto-Repair Frequency Diagnostic Class
 *
 * Monitors frequency of database auto-repairs as an indicator of
 * underlying data integrity issues.
 *
 * @since 1.6028.1049
 */
class Diagnostic_Database_Auto_Repair extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-auto-repair';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Auto-Repair Frequency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database is auto-repairing frequently (data corruption risk signal)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1049
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_db_auto_repair_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check if WP_ALLOW_REPAIR is enabled.
		if ( ! defined( 'WP_ALLOW_REPAIR' ) || ! WP_ALLOW_REPAIR ) {
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		// Check repair log.
		$repair_log = self::check_repair_log();

		if ( ! $repair_log['frequent_repairs'] ) {
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Database auto-repair is running frequently, indicating potential data corruption or hardware issues.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-auto-repair',
			'meta'         => array(
				'wp_allow_repair_enabled' => true,
				'repair_count_30_days'    => $repair_log['repair_count'],
				'last_repair_date'        => $repair_log['last_repair'],
			),
			'details'      => array(
				__( 'Frequent database repairs indicate underlying issues', 'wpshadow' ),
				__( 'May signal hardware problems or data corruption', 'wpshadow' ),
				__( 'Could lead to data loss if not addressed', 'wpshadow' ),
			),
			'recommendation' => __( 'Investigate server hardware, run full database integrity checks, and consider restoring from backup.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 6 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Check repair log.
	 *
	 * @since  1.6028.1049
	 * @return array Repair log analysis.
	 */
	private static function check_repair_log() {
		$repair_log = get_option( 'wpshadow_db_repair_log', array() );

		if ( empty( $repair_log ) ) {
			return array(
				'frequent_repairs' => false,
				'repair_count'     => 0,
				'last_repair'      => null,
			);
		}

		// Count repairs in last 30 days.
		$thirty_days_ago = strtotime( '-30 days' );
		$recent_repairs  = 0;
		$last_repair     = null;

		foreach ( $repair_log as $timestamp => $details ) {
			if ( $timestamp >= $thirty_days_ago ) {
				$recent_repairs++;
				$last_repair = max( $last_repair, $timestamp );
			}
		}

		// Flag as frequent if 3+ repairs in 30 days.
		$frequent_repairs = ( $recent_repairs >= 3 );

		return array(
			'frequent_repairs' => $frequent_repairs,
			'repair_count'     => $recent_repairs,
			'last_repair'      => $last_repair ? date( 'Y-m-d H:i:s', $last_repair ) : null,
		);
	}
}
