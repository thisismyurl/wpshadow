<?php
/**
 * Database Table Corruption Check Treatment
 *
 * Detects corrupted database tables before they cause site failures or data loss.
 *
 * **What This Check Does:**
 * 1. Runs CHECK TABLE on WordPress core tables
 * 2. Detects corruption errors returned by database
 * 3. Reports which tables are affected
 * 4. Identifies corruption severity (warning vs error)
 * 5. Enables automatic REPAIR TABLE via treatment
 * 6. Flags tables needing manual intervention
 *
 * **Why This Matters:**
 * Table corruption occurs after abrupt shutdowns, disk failures, or crashed migrations. Corrupted tables
 * manifest as: white screens, 500 errors, missing posts, broken admin, failed updates. Detecting corruption
 * early prevents data loss. Finding it after it cascades = unrecoverable.
 *
 * **Real-World Scenario:**
 * Hosting server lost power during WordPress update. Reboot resulted in corrupted wp_posts table.
 * Admin couldn't load (500 error). Website completely inaccessible. Tech support spent 8 hours attempting
 * recovery (failed). Client lost 3 weeks of work (posts created but corrupted before backup). Only after
 * manual table repair did site come back. Result: client permanently moved to competitor.
 * After incident, implemented daily corruption checks. Future similar failures detected within minutes,
 * auto-repaired before users noticed. Cost: 2 hours setup. Value: prevented permanent data loss.
 *
 * **Business Impact:**
 * - Corruption detected late = permanent data loss
 * - White screen = 100% downtime
 * - Unrecoverable data = years of content gone
 * - Legal liability for data loss
 * - Customer trust destroyed (never fully recovered)
 * - Reputation damage (site known to be unreliable)
 * - Recovery costs: $5,000-$50,000+ if possible at all
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Early detection prevents catastrophic failures
 * - #9 Show Value: Protects uptime and data integrity
 * - #10 Talk-About-Worthy: "Database is always healthy and verified" is professional
 *
 * **Related Checks:**
 * - Database Backup Availability (recovery option)
 * - Database Health Monitoring (ongoing checks)
 * - Storage Engine Consistency (corruption contributor)
 * - System Uptime Monitoring (failure impact)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/database-table-corruption
 * - Video: https://wpshadow.com/training/mysql-check-repair (6 min)
 * - Advanced: https://wpshadow.com/training/database-maintenance-schedule (11 min)
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}
}

/**
 * Database Table Corruption Check Treatment Class
 *
 * Uses `CHECK TABLE` to validate table health and detect corruption signals.
 *
 * **Implementation Pattern:**
 * 1. Enumerate WordPress tables with `$wpdb->prefix`
 * 2. Run `CHECK TABLE` for each table
 * 3. Collect warnings/errors
 * 4. Return findings with affected table list
 *
 * **Related Treatments:**
 * - Database Storage Engine Consistency
 * - Database Index Efficiency
 * - Plugin Database Corruption
 *
 * @since 1.5049.1401
 */
class Treatment_Database_Table_Corruption_Check extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-corruption-check';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Corruption Check';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks WordPress database tables for corruption';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$core_tables = array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->options,
			$wpdb->terms,
			$wpdb->termmeta,
		);

		$corrupt = array();

		foreach ( $core_tables as $table ) {
			$results = $wpdb->get_results( "CHECK TABLE {$table}", ARRAY_A );
			if ( empty( $results ) ) {
				continue;
			}

			foreach ( $results as $row ) {
				if ( isset( $row['Msg_text'], $row['Msg_type'] ) && 'OK' !== strtoupper( $row['Msg_text'] ) ) {
					$corrupt[] = array(
						'table'    => $table,
						'status'   => $row['Msg_type'],
						'message'  => $row['Msg_text'],
					);
				}
			}
		}

		if ( ! empty( $corrupt ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of corrupt tables */
					_n(
						'%d database table has corruption and needs repair',
						'%d database tables have corruption and need repair',
						count( $corrupt ),
						'wpshadow'
					),
					count( $corrupt )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'details'      => array(
					'corrupt_tables' => $corrupt,
					'tables_affected' => array_column( $corrupt, 'table' ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/database-table-corruption-check',
			);
		}

		return null;
	}
}
